# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

VISION Smart System: a Laravel 12 (PHP 8.2) helpdesk and ticketing app for an ISP ("VISION Technologies Limited"). The frontend is Blade, Alpine.js and Tailwind, with no SPA framework. The UI is bilingual, English and Bengali (`lang/bn.json`). `mobile_app/` is a Flutter client for the same backend.

- Local dev runs on Laragon with **MySQL** (`.env` has `DB_CONNECTION=mysql`).
- Tests run on **in-memory SQLite** (`phpunit.xml`). Keep migrations and queries portable across both databases — see the SQL portability trap below.
- Production is cPanel shared hosting with MySQL (see the section on deployment below).

## Common commands

```bash
composer run setup           # first-time setup: install, .env, key, migrate, npm build
composer run dev             # serve + queue:listen + pail + vite, run together

php artisan test                                   # full suite (composer run test clears config first)
php artisan test --filter=NotificationScopingTest  # single test class or method
php artisan test tests/Feature/SomeTest.php

vendor/bin/pint              # code style (Laravel Pint)
vendor/bin/pint --test       # style check only; CI fails on this
npm run build                # production assets (Vite)
```

Scheduled commands (all registered in `routes/console.php`, so production needs the `schedule:run` cron):

```bash
php artisan app:check-sla-breaches          # every 15 min
php artisan backup:database                 # daily 23:59
php artisan app:prune-location-history --days=7   # daily 03:00
```

Production also needs a **queue worker cron** so queued `SyncTicketToGoogleSheet` jobs run (otherwise ticket → sheet sync is deferred forever):

```
* * * * * cd <project> && php artisan queue:work --stop-when-empty --max-time=55 --tries=3 --sleep=2 >> /dev/null 2>&1
0 0 * * 0 cd <project> && php artisan queue:retry all >> /dev/null 2>&1
```

Flutter client (run from `mobile_app/`):

```bash
flutter pub get
flutter run
flutter test
flutter build apk --release
```

## Agent rules (from `.agents/rules/git_rules.md`)

- Work on `main`.
- **Never run `git push` unless the user explicitly asks.** For example, the user may say "git push koro".
- Run `php artisan test` before presenting changes.

## Architecture

### Roles and ticket visibility (the core rule)
`User::role` has these values: `super_admin`, `admin`, `noc`, `reseller`, `call_center`, `supervisor`, `senior_supervisor` and `technician`. Helpers include `isAdmin()` (which also returns true for super_admin), `isSuperAdmin()`, `isNoc()`, `isReseller()`, `isCallCenter()`, `isSupervisorLevel()`, `isTechnician()` and similar.

Ticket visibility is centralized in **`Ticket::scopeForUser()`** (`app/Models/Ticket.php`):
- super_admin sees everything.
- A reseller sees only the tickets it created.
- A technician sees only the tickets assigned to it.
- Every other role sees tickets assigned to them or created by them, plus *unassigned* tickets. The exception is call_center, which never sees unassigned tickets created by resellers.

Reuse `->forUser($user)` for any new ticket query, list, search or report instead of re-implementing the rules. `NotificationService::send()` also checks `forUser` before it creates a notification (covered by `tests/Feature/NotificationScopingTest.php`).

Authorization is mostly enforced **inside controllers** with `abort(403)` checks. The `role` middleware alias (`RoleMiddleware`) exists but routes rarely use it. The `/dashboard` route closure in `routes/web.php` builds different aggregated stats per role, so follow that per-role branching when you add dashboard or report logic.

### SQL portability trap
Aggregated stat queries are written with `selectRaw`. The **web** `/dashboard` closure and `ReportController` use MySQL-only functions (`TIMESTAMPDIFF`, `NOW()`), and no test renders those pages, so a green `php artisan test` does **not** prove they work. `ApiDashboardController` deliberately avoids this by interpolating PHP's `now()` into the SQL instead of calling `NOW()`. Follow the API controller's pattern in new code, and verify any change to the web dashboard or reports against MySQL by hand.

### Ticket domain
- `ticket_key` uses the format `YYMMDDNNN` and comes from `Ticket::generateKey()`. The key scans existing keys for the day's max sequence to avoid duplicate-key collisions, so don't simplify it to a count.
- Tickets support subtasks (`parent_id`), links between tickets (`TicketLink`), merging (`merged_into_id`), labels, categories (`TicketCategory`), areas (`Area`), POP offices (`PopOffice`), attachments, internal notes, messages (with replies, reactions and private messages), canned responses, CSAT and SLA `due_at`.
- The audit trail lives in `TicketHistory`. `TicketObserver` and `TicketMessageObserver` write it, and `AppServiceProvider` registers both observers. Separately, `ActivityLog` records app-wide admin actions (`/activity-logs`).
- `BoardController` (`/board`) is the drag-and-drop kanban view; `POST /board/move` changes a ticket's status.
- In-app notifications go through `NotificationService`. **Email is sent separately, directly from controllers.** For example, `TicketController` calls `Mail::to(...)->send(new TicketResolved(...))` inside a try/catch, gated on user preferences such as `notify_on_resolve` and `notify_on_assign`.
- SLA: `SlaPolicy` together with `CheckSlaBreaches` (a scheduled command) flags breaches and sets `sla_notified_at`.
- Team roster and on-duty status: `User::TEAMS`, `User::SHIFTS`, `isOnDuty()`, `ensureCurrentShiftDate()` and `RosterController`. `Team` and `Area` are editable lookup tables (`/teams`, `/areas`).

### Firebase (push + realtime)
`FirebaseService` handles both FCM push and Realtime Database ticket sync, and `TicketObserver` / `TicketMessageObserver` call it. Everything is gated on `FIREBASE_ENABLED` plus `config/firebase.php` credentials, so the service no-ops when unconfigured — keep that guard, since tests and most dev setups run without Firebase. Devices register their token via `POST /api/user/fcm-token`; the Flutter side is `lib/services/push_service.dart` and `lib/services/firebase_realtime_service.dart`.

### Ticket type: internal vs external
`tickets.ticket_type` is `internal` or `external` (default `external`). The create form (`resources/views/tickets/create.blade.php`) shows a two-tab Alpine.js switcher; **internal** tickets collect only Title, Description, Category, Priority, Assign To, while **external** tickets hide Title/Description (auto-generated from client name + category) and surface Client ID / Client Name / Area / `address` (separate from `area`) / Complaint Source / ONU Power. Only external tickets are dispatched to Google Sheet — `GoogleSheetSyncService::syncTicketCreated/Updated` early-return when `ticket_type === 'internal'`.

### Google Sheet sync (queued)
`GoogleSheetSyncService` posts ticket create/update events to a Google Apps Script webhook, gated on `google_sheet_sync_enabled` and `google_sheet_webhook_url` settings (edited from `/settings`). The service is **never called directly from controllers or the observer** — instead they dispatch `App\Jobs\SyncTicketToGoogleSheet` with `($ticketId, 'create'|'update', $remarks?)`. This keeps ticket create/status endpoints under 200ms even when the Apps Script webhook is slow. The job uses `tries=3`, `timeout=30`, `backoff=10`. In production `QUEUE_CONNECTION=database` and a per-minute cron runs `php artisan queue:work --stop-when-empty --max-time=55 --tries=3 --sleep=2`. Local dev typically keeps `QUEUE_CONNECTION=sync` so webhook calls happen inline (no worker to run). Column F = ticket key, Column O = Current Status (`Assigned`, `Pending`, `Processing`, `Solved`). The service also humanises slugs — e.g. `line_fault` → `Line Fault` — via `mapCategoryName()` fallback. Errors are logged and swallowed; sync failures never block ticket writes.

### Live location tracking
`UserLocation` stores each user's latest position (plus accuracy, battery, speed and an `is_sharing` flag), with a history table pruned by `app:prune-location-history`. Web side: `LocationMapController` at `/live-map` (Leaflet). Mobile side: `lib/services/location_service.dart` posting to `/api/user/location`. Sharing is opt-in per user — respect `is_sharing` in any new query.

### Other cross-cutting modules
- **2FA:** `TwoFactorService` and `TwoFactorController`. Enrollment lives under `/two-factor` (authenticated); the login challenge at `/two-factor-challenge` runs **outside** the `auth` middleware group in a partially-authenticated session state.
- **Backups:** `DatabaseBackupService` with `BackupController` (`/settings/backup/*`) and the `backup:database` command. Note `SettingController::backupExport/backupRestore` is a separate, older settings-only export.

### Cross-cutting behavior in `AppServiceProvider`
- `Model::preventLazyLoading()` is on outside production, so eager-load relations or you'll get exceptions in dev and tests.
- A global `View::composer('*')` shares `customMenuLinks`, the header notice settings (from the `Setting` model) and `unreadKbCount` with every view. Each lookup is individually wrapped in try/catch so views still render before migrations run — keep that.
- The `Carbon::toBn()` macro converts dates and digits to Bengali when the locale is `bn`. The `SetLocale` middleware picks the locale from the session, then `user->locale`, then falls back to `en`.
- HTTPS is forced when `APP_URL` is https or when `X-Forwarded-Proto` is set.

### Mobile API and Flutter app
- `routes/api.php` serves the mobile app under `/api`, using Sanctum token auth (`POST /api/login` returns a token). Controllers are in `app/Http/Controllers/Api/` and return JSON. They must scope tickets with `Ticket::forUser()` too. Tests: `tests/Feature/MobileApiTest.php` and `MobileDashboardAndAdminApiTest.php`.
- cPanel's FastCGI strips the `Authorization` header. To work around that, `AppServiceProvider` registers `Sanctum::getAccessTokenFromRequestUsing()`, which falls back to `X-Authorization`, `X-Api-Token`, `REDIRECT_HTTP_AUTHORIZATION` and `?token=`. `public/.htaccess` also forwards the headers. Keep both in place.
- In `mobile_app/lib/`: HTTP calls go through `services/api_service.dart`, session and shared state through `services/app_state.dart`, and models are in `models/`. Nested relations such as `assigned_to` and `created_by` are parsed from the API JSON, so changing the shape of an API response can break the app.
- **Background service:** `services/background_service.dart` (`BgService`) runs a Flutter foreground service via `flutter_background_service` that keeps location updates (5 min) and notification polling (30 sec) alive after the app is swiped away. It is initialised in `main.dart` and started on login (`login_screen.dart`) / stopped on logout (`profile_screen.dart`). Android requires `ACCESS_BACKGROUND_LOCATION`, `FOREGROUND_SERVICE`, `FOREGROUND_SERVICE_LOCATION`, and the `id.flutter.flutter_background_service.BackgroundService` declaration in `AndroidManifest.xml` with `foregroundServiceType="location|dataSync"` — don't remove any of these.
- CI: `.github/workflows/ci.yml` runs `pint --test` plus `php artisan test` on every push and PR. `build_apk.yml` builds the release APK when `mobile_app/**` changes and publishes it to the `latest` GitHub release.

### Knowledge Base
`BlogPostController`, `KbArticleController` and `KbCategoryController` are mounted at `/knowledge-base`. Legacy routes (`/knowledge-base-blogs-*`) and redirects from `/blogs` and `/kb` stay for backward compatibility with external links, so don't remove them.

### Attendance device integration
`.env` carries `ATTENDANCE_DEVICE_IP`, `ATTENDANCE_DEVICE_PORT`, `ATTENDANCE_DNS_IP`, and `ATTENDANCE_SERVER_LAN_IP` for the LAN biometric / Hikvision webhook flow (see recent commits `829c177`, `2529bb9`, `a7cebe0`, `6871f6f`). These control the Hikvision ISAPI endpoint and the server-side DNS/LAN diagnostics shown on `/attendance/*` admin pages. Leave the keys in place even when a dev machine can't reach the device — the controllers already guard the calls.

### Disabled or removed modules (stale references)
- **WhatsApp:** the WhatsApp routes in `routes/web.php` are commented out. `WhatsAppController`, `WhatsAppService` and the Node bridge are no longer in the repo, and only `WA_INTERNAL_SECRET` is left in `.env` (stale WhatsApp entries also remain in `.claude/settings.json`). The feature doesn't exist, so don't uncomment those routes.

## Deployment (cPanel)
- Production target is `portal.visiontech.com.bd`. Deployment uses cPanel's File Manager to upload a zip and phpMyAdmin for the database. The guide is `DEPLOY_CPANEL.md` (in Bengali); also see `deploy/DEPLOY_GUIDE.md` and `deploy/.env.cpanel-template`.
- `routes/web.php` also exposes `/deploy-webhook`, which — given the right `DEPLOY_WEBHOOK_SECRET` — shells out to `git fetch` plus **`git reset --hard origin/main`** on the production checkout. Anything pushed to `main` can therefore land in production through it, and any uncommitted file on the server is destroyed. Treat changes to that route with care.
- `.cpanel.yml` copies the repo to the deploy path.
- `environment.php` is a standalone script you open in a browser to check PHP and server requirements on the host.
- Shared hosting constrains production: no long-running processes can be assumed, and queue workers and the scheduler depend on cron. Keep these deploy scripts working when you touch them.

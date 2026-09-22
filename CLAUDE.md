# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

VISION Smart System: a Laravel 12 (PHP 8.2) helpdesk and ticketing app for an ISP ("VISION Technologies Limited"). The frontend is Blade, Alpine.js and Tailwind, with no SPA framework. The UI is bilingual, English and Bengali (`lang/bn.json`).

- Local dev runs on Laragon with **MySQL** (`.env` has `DB_CONNECTION=mysql`).
- Tests run on **in-memory SQLite** (`phpunit.xml`). Keep migrations and queries portable across both databases.
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

php artisan app:check-sla-breaches   # the scheduled SLA check (runs every 15 min via routes/console.php)
```

## Agent rules (from `.agents/rules/git_rules.md`)

- Work on `main`.
- **Never run `git push` unless the user explicitly asks.** For example, the user may say "git push koro".
- Run `php artisan test` before presenting changes.

## Architecture

### Roles and ticket visibility (the core rule)
`User::role` has these values: `super_admin`, `admin`, `noc`, `reseller`, `call_center`, `supervisor` and `senior_supervisor`. Helpers include `isAdmin()` (which also returns true for super_admin), `isSuperAdmin()`, `isNoc()`, `isReseller()`, `isCallCenter()`, `isSupervisorLevel()` and similar.

Ticket visibility is centralized in **`Ticket::scopeForUser()`** (`app/Models/Ticket.php`):
- super_admin sees everything.
- A reseller sees only the tickets it created.
- Every other role sees tickets assigned to them or created by them, plus *unassigned* tickets. The exception is call_center, which never sees unassigned tickets created by resellers.

Reuse `->forUser($user)` for any new ticket query, list, search or report instead of re-implementing the rules. `NotificationService::send()` also checks `forUser` before it creates a notification (covered by `tests/Feature/NotificationScopingTest.php`).

Authorization is mostly enforced **inside controllers** with `abort(403)` checks. The `role` middleware alias (`RoleMiddleware`) exists but routes rarely use it. The `/dashboard` route closure in `routes/web.php` builds different aggregated stats per role, so follow that per-role branching when you add dashboard or report logic.

### Ticket domain
- `ticket_key` uses the format `YYMMDDNNN` and comes from `Ticket::generateKey()`. The key scans existing keys for the day's max sequence to avoid duplicate-key collisions, so don't simplify it to a count.
- Tickets support subtasks (`parent_id`), links between tickets (`TicketLink`), merging (`merged_into_id`), labels, categories (`TicketCategory`), POP offices (`PopOffice`), attachments, internal notes, messages (with replies, reactions and private messages), CSAT and SLA `due_at`.
- The audit trail lives in `TicketHistory`. `TicketObserver` and `TicketMessageObserver` write it, and `AppServiceProvider` registers both observers.
- Mobile push (FCM) goes through `FirebaseService`, which `TicketObserver` and `TicketMessageObserver` call. Devices register their token via `POST /api/user/fcm-token`.
- In-app notifications go through `NotificationService`. **Email is sent separately, directly from controllers.** For example, `TicketController` calls `Mail::to(...)->send(new TicketResolved(...))` inside a try/catch, gated on user preferences such as `notify_on_resolve` and `notify_on_assign`.
- SLA: `SlaPolicy` together with `CheckSlaBreaches` (a scheduled command) flags breaches and sets `sla_notified_at`.
- Team roster and on-duty status: `User::TEAMS`, `User::SHIFTS`, `isOnDuty()`, `ensureCurrentShiftDate()` and `RosterController`.

### Cross-cutting behavior in `AppServiceProvider`
- `Model::preventLazyLoading()` is on outside production, so eager-load relations or you'll get exceptions in dev and tests.
- A global `View::composer('*')` shares `customMenuLinks`, the header notice settings (from the `Setting` model) and `unreadKbCount` with every view.
- The `Carbon::toBn()` macro converts dates and digits to Bengali when the locale is `bn`. The `SetLocale` middleware picks the locale from the session, then `user->locale`, then falls back to `en`.
- HTTPS is forced when `APP_URL` is https or when `X-Forwarded-Proto` is set.

### Mobile API and Flutter app
- `routes/api.php` serves the mobile app under `/api`, using Sanctum token auth (`POST /api/login` returns a token). Controllers are in `app/Http/Controllers/Api/` and return JSON. They must scope tickets with `Ticket::forUser()` too. Tests: `tests/Feature/MobileApiTest.php` and `MobileDashboardAndAdminApiTest.php`.
- cPanel's FastCGI strips the `Authorization` header. To work around that, `AppServiceProvider` registers `Sanctum::getAccessTokenFromRequestUsing()`, which falls back to `X-Authorization`, `X-Api-Token`, `REDIRECT_HTTP_AUTHORIZATION` and `?token=`. `public/.htaccess` also forwards the headers. Keep both in place.
- `mobile_app/` is a Flutter client. HTTP calls go through `lib/services/api_service.dart`, and models are in `lib/models/`. Nested relations such as `assigned_to` and `created_by` are parsed from the API JSON, so changing the shape of an API response can break the app.
- CI: `.github/workflows/ci.yml` runs `pint --test` plus `php artisan test` on every push and PR. `build_apk.yml` builds the release APK when `mobile_app/**` changes and publishes it to the `latest` GitHub release.

### Knowledge Base
`BlogPostController`, `KbArticleController` and `KbCategoryController` are mounted at `/knowledge-base`. Legacy routes (`/knowledge-base-blogs-*`) and redirects from `/blogs` and `/kb` stay for backward compatibility with external links, so don't remove them.

### Disabled or removed modules (stale references)
- **WhatsApp:** the WhatsApp routes in `routes/web.php` are commented out. `WhatsAppController`, `WhatsAppService` and the Node bridge are no longer in the repo, and only `WA_INTERNAL_SECRET` is left in `.env`. The feature doesn't exist, so don't uncomment those routes.

## Deployment (cPanel)
- Production target is `portal.visiontech.com.bd`. Deployment uses cPanel's File Manager to upload a zip and phpMyAdmin for the database. The guide is `DEPLOY_CPANEL.md` (in Bengali); also see `deploy/DEPLOY_GUIDE.md` and `deploy/.env.cpanel-template`.
- `.cpanel.yml` copies the repo to the deploy path.
- `environment.php` is a standalone script you open in a browser to check PHP and server requirements on the host.
- Shared hosting constrains production: no long-running processes can be assumed, and queue workers and the scheduler depend on cron. Keep these deploy scripts working when you touch them.

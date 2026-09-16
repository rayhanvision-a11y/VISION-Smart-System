# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

VISION Smart System — a Laravel 12 (PHP 8.2) ISP helpdesk/ticketing app ("VISION Technologies Limited") with an integrated WhatsApp broadcast/chat module. Blade + Alpine.js + Tailwind frontend (no SPA framework). SQLite by default (`DB_CONNECTION=sqlite`).

## Common commands

```bash
composer install
npm install

php artisan serve            # app server
npm run dev                  # Vite dev server (Tailwind/Alpine assets)
composer run dev             # runs serve + queue:listen + pail + vite concurrently

php artisan test             # full test suite (also: composer run test)
php artisan test --filter=TestName   # single test
php artisan test tests/Feature/SomeTest.php

vendor/bin/pint               # PHP code style (Laravel Pint)

php artisan migrate
php artisan queue:listen --tries=1 --timeout=0

node whatsapp_server.cjs      # standalone WhatsApp bridge (Express + Baileys), must run on port 3000 alongside Laravel
```

## Architecture

### Core ticketing domain
Standard Laravel MVC under `app/Http/Controllers`, `app/Models`, `routes/web.php`. Key model: `Ticket` (`app/Models/Ticket.php`) — has a human-readable `ticket_key` (format `YYMMDDNNN`, generated in `Ticket::generateKey()`), supports parent/subtask relations, ticket-to-ticket links (`TicketLink`), merging (`merged_into_id`), labels (many-to-many), attachments, notes, messages, and history (audit trail via `TicketHistory`, written by `TicketObserver`/`TicketMessageObserver`).

Roles live on `User::role` (`super_admin`, `admin`, `noc`, `reseller`) with helper methods (`isAdmin()`, `isNoc()`, `isReseller()`, `isSuperAdminOnly()`). Route-level role gating uses `RoleMiddleware` (`role:admin,noc` style). Dashboard/reporting behavior branches heavily by role — see the `/dashboard` closure in `routes/web.php` for the pattern of per-role aggregated stats queries (admin sees global stats, NOC sees only tickets assigned to them, reseller sees only tickets they created).

SLA handling: `app/Console/Commands/CheckSlaBreaches.php` (scheduled command) + `SlaPolicy` model/controller compute `due_at` and flag breaches.

Notifications: `app/Services/NotificationService.php` centralizes creating `Notification` records and dispatching related mail (`app/Mail/*`, e.g. `TicketAssigned`, `TicketResolved`, `TicketReopened`).

Inbound email → ticket: `InboundEmailController@handle` is a public webhook (`/webhooks/inbound-email`, no auth, protected by a shared secret) that creates ticket messages from replies.

Knowledge Base module (`BlogPostController`, `KbArticleController`) is mounted at `/knowledge-base` with legacy aliased routes (`/knowledge-base-blogs-*`, and redirects from old `/blogs` and `/kb` paths) kept for backward compatibility — don't remove these without checking for external links/bookmarks.

### WhatsApp integration (two-process architecture)
This is the non-obvious part of the system: WhatsApp features run as a **separate Node.js process**, not inside Laravel.

- `whatsapp_server.cjs` — standalone Express server (port 3000) using `@whiskeysockets/baileys` for the WhatsApp Web protocol. Keeps **per-user sessions** in memory (`sessions` Map keyed by user id), each with its own Baileys auth folder (`auth_info_<userId>/`) and JSON store file (`baileys_store_<userId>.json`) persisted to disk. Handles QR login, chat/contact sync, sending messages/images, scheduled broadcasts (cron via `setInterval`, checked every minute), saved contact lists, and message templates (global/admin-shared vs personal per-user).
- `app/Http/Controllers/WhatsAppController.php` — Laravel proxy. Every request from the browser hits this controller first, which forwards to the Node server via `Illuminate\Support\Facades\Http`, attaching `X-WA-User` (from `auth()->id()`), `X-WA-Admin` (from role), and `X-Internal-Token` (shared secret, `WA_INTERNAL_SECRET` env var) headers. The Node server rejects any request missing/mismatching that internal token — it must never be exposed directly to the internet.
- Because sessions are per-user and in-memory in the Node process, **both processes must be running together** for WhatsApp features to work; restarting `whatsapp_server.cjs` drops in-flight (non-persisted) state but reloads persisted store/auth files from disk.
- Global (admin) vs personal templates/saved-lists distinction is enforced by the `X-WA-Admin` header set by the Laravel side, not re-checked independently by Node beyond that header.

### cPanel / shared-hosting considerations
`.cpanel.yml` and `environment.php` (see recent commit history) exist for deploying to shared cPanel hosting; there's a standalone diagnostic script for checking the cPanel PHP environment. Keep deploy-related scripts working if touched — this app appears to run in a constrained shared-hosting environment in addition to local dev.

## Notes for future changes
- `auth_info_*/` directories and `baileys_store_*.json`/`global_templates.json` files are runtime WhatsApp session state, not source — don't hand-edit or commit sensitive contents.
- When adding new dashboard/report logic, follow the existing per-role branching pattern in `routes/web.php` rather than introducing a generic query for all roles.

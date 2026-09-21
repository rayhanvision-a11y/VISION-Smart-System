# VISION Smart System

Laravel 12 (PHP 8.2) helpdesk and ticketing app for VISION Technologies Limited (ISP). Blade + Alpine.js + Tailwind frontend, bilingual (English / বাংলা).

## Requirements

- PHP 8.2+
- MySQL 5.7+ / 8.x (local dev via Laragon)
- Node 18+ and npm
- Composer 2

## First-time setup

```bash
composer run setup     # installs, copies .env, generates key, migrates, builds assets
```

Then edit `.env` for your local DB, mail and app URL.

## Development

```bash
composer run dev       # serve + queue:listen + pail + vite (all together)
```

## Testing

Tests run on in-memory SQLite (see `phpunit.xml`).

```bash
php artisan test                                   # full suite
php artisan test --filter=NotificationScopingTest  # single test
```

## Code style

```bash
vendor/bin/pint          # apply
vendor/bin/pint --test   # check only (used in CI)
```

## Scheduled jobs

`routes/console.php` schedules `app:check-sla-breaches` every 15 minutes. On production, install the Laravel scheduler cron:

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## Roles

`User::role` values: `super_admin`, `admin`, `noc`, `reseller`, `call_center`, `supervisor`, `senior_supervisor`.

Ticket visibility is centralised in `Ticket::scopeForUser()` — always use `->forUser($user)` when querying tickets. See `CLAUDE.md` for the full rule.

## Deployment (cPanel)

See `DEPLOY_CPANEL.md` (Bengali) and `deploy/DEPLOY_GUIDE.md`. Production target: `portal.visiontech.com.bd`.

## Project docs

- `CLAUDE.md` — architecture, conventions, and rules for AI-assisted development.
- `.agents/rules/git_rules.md` — git workflow rules.

## License

MIT.

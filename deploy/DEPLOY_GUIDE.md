# VISION Smart System — cPanel Deploy Guide (Terminal Edition)

Deploy target: `https://portal.visiontech.com.bd/`
App root on server: `/home/CPANELUSER/public_html/domains/subdomains/portal.visiontech.com.bd`

> Replace `CPANELUSER` everywhere below with your actual cPanel username.
> This guide assumes you have **cPanel Terminal (SSH)** access.

---

## What's in this folder

| File | Purpose |
|---|---|
| `isp-tickets-deploy-YYYYMMDD-HHMM.zip` | Fresh app bundle (no `vendor/`, no `node_modules/`, no runtime junk). |
| `.env.cpanel-template` | Copy to `.env` on the server and fill in DB + mail creds. |
| `DEPLOY_GUIDE.md` | This file. |

Bundle is intentionally slim (~2 MB). `vendor/` is installed on the server via `composer install` — much faster than uploading it.

---

## 0. Pre-flight (once per server)

In cPanel:

1. **MultiPHP Manager** → set your domain to **PHP 8.2+**.
2. **Select PHP Version** → enable extensions:
   `bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, zip`
3. **MySQL Databases** → create:
   - Database: `CPANELUSER_ispdb`
   - User: `CPANELUSER_ispuser` (strong password)
   - Add user to DB with **ALL PRIVILEGES**. Save all three values.

---

## 1. Upload the zip

Two options:

**Option A — cPanel File Manager**
Upload `isp-tickets-deploy-*.zip` into
`public_html/domains/subdomains/portal.visiontech.com.bd/`.

**Option B — Terminal (scp/rsync from your machine)**
```bash
scp isp-tickets-deploy-*.zip CPANELUSER@portal.visiontech.com.bd:~/public_html/domains/subdomains/portal.visiontech.com.bd/
```

---

## 2. Open cPanel Terminal and extract

```bash
cd ~/public_html/domains/subdomains/portal.visiontech.com.bd
unzip -o isp-tickets-deploy-*.zip
rm isp-tickets-deploy-*.zip
```

Verify:
```bash
ls -la    # should show app/  bootstrap/  config/  public/  routes/  storage/  artisan  composer.json  .env.cpanel-template
```

---

## 3. Configure `.env`

```bash
cp .env.cpanel-template .env
nano .env      # or:  vi .env
```

Set these values:

- `APP_URL=https://portal.visiontech.com.bd`
- `DB_DATABASE=CPANELUSER_ispdb`
- `DB_USERNAME=CPANELUSER_ispuser`
- `DB_PASSWORD=<the password>`
- `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD` — your SMTP.
- `WA_INTERNAL_SECRET=<any long random string>`
- Leave `APP_KEY=` blank — step 4 fills it.

Save & exit (`Ctrl+O`, `Enter`, `Ctrl+X` in nano).

---

## 4. Install dependencies & run setup

```bash
# 4a. Composer (production install, no dev packages)
composer install --no-dev --optimize-autoloader --no-interaction

# 4b. Generate app key
php artisan key:generate --force

# 4c. Run migrations (creates all tables)
php artisan migrate --force

# 4d. Storage symlink for public uploads
php artisan storage:link

# 4e. Cache config/routes/views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If `composer` command isn't found, try `composer.phar` or the full path
(often `/opt/cpanel/composer/bin/composer` or `~/composer.phar`).

---

## 5. Set the web root to `public/`

The Laravel app **must** serve from `public/` — otherwise `.env` is exposed.

- cPanel → **Domains** → find `portal.visiontech.com.bd` → **Edit Document Root**
  → set to `public_html/domains/subdomains/portal.visiontech.com.bd/public` → Save.

If your host locks Document Root, put this `.htaccess` in the project root
(one level above `public/`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 6. Permissions

```bash
cd ~/public_html/domains/subdomains/portal.visiontech.com.bd
chmod -R 775 storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
```

---

## 7. Seed the first admin

Migrations don't create users. Quickest path — Tinker:

```bash
php artisan tinker
```
Then paste:
```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@visiontech.com.bd',
    'password' => bcrypt('ChangeMeNow!'),
    'role' => 'super_admin',
]);
exit
```

Log in at `https://portal.visiontech.com.bd/` and change the password from the Users page.

---

## 8. Cron for SLA breaches & scheduled jobs

cPanel → **Cron Jobs** → add (runs every 5 minutes):

```
*/5 * * * * /usr/local/bin/php /home/CPANELUSER/public_html/domains/subdomains/portal.visiontech.com.bd/artisan schedule:run >> /dev/null 2>&1
```

Confirm the PHP path with `which php` in Terminal.

---

## 9. Verify

```bash
php artisan about       # summary of env/config
tail -f storage/logs/laravel.log   # watch for errors while you browse the site
```

Open `https://portal.visiontech.com.bd/` in a browser — dashboard should load.

---

## Updating later (incremental deploy)

For a code-only update (no schema changes):

```bash
cd ~/public_html/domains/subdomains/portal.visiontech.com.bd
# upload/extract the new zip (skip .env!)
unzip -o isp-tickets-deploy-*.zip -x ".env.cpanel-template"
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
```

Your existing `.env`, `storage/app/`, and DB data are untouched.

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| **500 / white page** | `tail -50 storage/logs/laravel.log`. Usually `.env` DB creds or permissions. |
| **CSS/JS 404** | Document Root not set to `/public`. See step 5. |
| **"could not find driver"** | Enable `pdo_mysql` in Select PHP Version. |
| **Uploaded files 404** | Re-run `php artisan storage:link`. |
| **"Class not found" after update** | `composer dump-autoload -o && php artisan config:clear` |
| **Cache showing old code** | `php artisan optimize:clear` then re-cache. |

---

## WhatsApp module (optional)

The WhatsApp bridge (`whatsapp_server.cjs`) is **not** included in this bundle
— it requires a persistent Node.js process on port 3000, which shared cPanel
usually can't run. Deploy it separately on a VPS if you need WhatsApp features,
and point Laravel to it via `WA_BRIDGE_URL` in `.env`.

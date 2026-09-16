# cPanel এ ডিপ্লয় করার গাইড (File Manager + phpMyAdmin + cPanel Terminal)

**টার্গেট ডোমেইন:** `https://portal.visiontech.com.bd/`  
**ফোল্ডার ডিরেক্টরি:** `public_html/domains/subdomains/portal.visiontech.com.bd` (আপনার সাব-ডোমেইনের ফোল্ডার)

এই গাইডে আগে থেকে তৈরি করা ডিপ্লয়মেন্ট প্যাকেজ (`isp-tickets-deploy.zip`) ব্যবহার করা হয়েছে, যার মধ্যে `vendor/` (Composer ডিপেন্ডেন্সি) এবং `public/build/` (কম্পাইল করা CSS/JS) আগে থেকেই অন্তর্ভুক্ত আছে। ফলে **সার্ভারে আলাদাভাবে Composer এর প্রয়োজন হবে না** — cPanel এর File Manager, phpMyAdmin এবং cPanel Terminal ব্যবহার করলেই চলবে।

> **WhatsApp মেডিউল ও cPanel Terminal সার্ভিস:** জিপ (zip) ফাইলের মধ্যে `whatsapp_server.cjs`, `package.json` এবং `whatsapp-service/` ফোল্ডার (start/stop/watchdog স্ক্রিপ্টসহ) অন্তর্ভুক্ত আছে। আপনার cPanel এ **Terminal** সুবিধা থাকায়, আলাদা "Setup Node.js App" টুল ছাড়াই ব্যাকগ্রাউন্ড সার্ভিস ও Cron Watchdog দিয়ে হোয়াটসঅ্যাপ বট সার্ভিস চালু রাখতে পারবেন — বিস্তারিত **ধাপ ৮** দেখুন।

---

## 0. শুরু করার আগের প্রস্তুতি (Pre-checks)

1. আপনার ডোমেইনের জন্য PHP ভার্সন **8.2 বা তার বেশি** সেট করুন:
   * cPanel ➔ **MultiPHP Manager** (অথবা **Select PHP Version**) ➔ ডোমেইন নির্বাচন করুন ➔ PHP 8.2+ সেভ করুন।
2. একই PHP অপশনে নিচের এক্সটেনশনগুলো টিক দিয়ে **এনাবেল (Enable)** করুন:
   `bcmath, ctype, curl, dom, fileinfo, gd, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, zip`

---

## 1. MySQL ডেটাবেস তৈরি করুন

cPanel ➔ **MySQL® Databases** এ যান:

1. **Create New Database** এর নিচে একটি নাম লিখুন (যেমন: `ispdb`) ➔ **Create Database** এ ক্লিক করুন। cPanel ইউজারনাম প্রিফিক্সসহ পুরো নাম নোট করুন (যেমন: `cpaneluser_ispdb`)।
2. **MySQL Users** এর নিচে নতুন ইউজার ও স্ট্রং পাসওয়ার্ড দিয়ে ইউজার তৈরি করুন। প্রিফিক্সসহ ইউজারনাম (যেমন: `cpaneluser_ispuser`) ও পাসওয়ার্ড সেভ করে রাখুন।
3. **Add User to Database** সেকশনে গিয়ে তৈরি করা ইউজারকে ডেটাবেসে যুক্ত করুন এবং **ALL PRIVILEGES** টিক দিয়ে সেভ করুন।

এই ৩টি তথ্য (Database Name, Username, Password) পরবর্তী ধাপ ৩-এ লাগবে।

---

## 2. প্রজেক্ট ফাইল আপলোড করুন

cPanel ➔ **File Manager** ➔ ডিরেক্টরিতে যান:  
`public_html/domains/subdomains/portal.visiontech.com.bd` (ফোল্ডারটি না থাকলে তৈরি করে নিন)।

1. **Upload** এ ক্লিক করে `isp-tickets-deploy.zip` ফাইলটি আপলোড করুন।
2. আপলোড শেষ হলে zip ফাইলের উপর রাইট-ক্লিক করে **Extract** করুন। সব ফাইল (`app/`, `public/`, `vendor/` ইত্যাদি) এই ফোল্ডারে আনজিপ হবে।
3. জায়গা বাঁচাতে আনজিপ করার পর zip ফাইলটি ডিলিট করে দিন।

### ওয়েব রুট হিসেবে `public` ফোল্ডার সেট করা (অত্যন্ত জরুরি)

লারাভেল অ্যাপ অবশ্যই `public/` সাব-ফোল্ডার থেকে রান করতে হবে, যাতে `.env` এবং সোর্স কোড ইন্টারনেটে লিক না হয়।

* cPanel ➔ **Domains** (অথবা **Subdomains**) ➔ `portal.visiontech.com.bd` খুঁজে বের করুন ➔ **Edit Document Root** এ গিয়ে সেট করুন:  
  `public_html/domains/subdomains/portal.visiontech.com.bd/public`  
  ➔ Save করুন।

**যদি cPanel এ Document Root পরিবর্তন করতে না দেয়** (কিছু শেয়ার্ড হোস্টিংয়ে লক থাকে), তবে বিকল্প পদ্ধতি ব্যবহার করুন: আপলোড করা প্রজেক্টের রুট থেকে `htaccess-root-fallback.txt` ফাইলটি নিয়ে নাম পরিবর্তন করে `.htaccess` করুন এবং এটি `public_html/domains/subdomains/portal.visiontech.com.bd/` ফোল্ডারে (public ফোল্ডারের বাইরে) রাখুন।

---

## 3. `.env` ফাইল কনফিগার করুন

আপলোড করা ফাইলের সাথে `.env` থাকবে না, তার বদলে `.env.cpanel-template` ফাইল থাকবে।

1. File Manager থেকে `.env.cpanel-template` ফাইলটির একটি কপি বানিয়ে সেটির নাম দিন **`.env`** (শুরুতে ডট সহ)।
2. `.env` ফাইলটি एडिट (Edit) করে নিচের তথ্যগুলো বসান:
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — ধাপ ১ থেকে পাওয়া ডেটাবেসের তথ্য।
   - `APP_URL=https://portal.visiontech.com.bd`
   - `WA_INTERNAL_SECRET` — যেকোনো বড় র‍্যান্ডম সিক্রেট স্ট্রিং।
   - ইমেইল সেটিংস (`MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`) — cPanel **Email Accounts** বা আপনার SMTP তথ্য।
   - `APP_KEY` ফাঁকা রাখুন — পরের ধাপে ইনস্টলার এটি স্বয়ংক্রিয়ভাবে তৈরি করবে।
3. Save করুন।

---

## 4. ওয়ান-টাইম ইনস্টলার রান করুন (Installer Script)

টার্মিনাল ছাড়াই সেটআপের কাজগুলো করতে ইনস্টলার স্ক্রিপ্ট ব্যবহার করুন:

1. ব্রাউজারে ভিকিট করুন: **`https://portal.visiontech.com.bd/installer.php`**
2. **"Run Setup Now"** বাটনে ক্লিক করুন। এটি স্বয়ংক্রিয়ভাবে:
   - `APP_KEY` জেনারেট করবে।
   - সব ডেটাবেস মাইগ্রেশন রান করবে (টেবিল তৈরি করবে)।
   - `public/storage` সিমলিঙ্ক লিংক তৈরি করবে।
   - ক্যাশ ক্লিয়ার ও অপটিমাইজ করবে।
   - ইউজার টেবিল খালি থাকলে ৩টি ডিফল্ট ডেমো ইউজার তৈরি করবে (`admin@isp.com`, `noc@isp.com`, `reseller@isp.com`, পাসওয়ার্ড: `password`)।
3. সবগুলোতে সবুজ টিকচিহ্ন আসলে কাজ শেষ।
4. **সিকিউরিটির জন্য `public/installer.php` ফাইলটি সাথে সাথেই ডিলিট করে দিন।**
5. একইভাবে ডায়াগনস্টিক হয়ে গেলে প্রজেক্ট রুট থেকে `environment.php` ফাইলটিও ডিলিট করে দিন।

---

## 5. লগইন করুন এবং ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন

`https://portal.visiontech.com.bd/` এ যান এবং `admin@isp.com` / `password` দিয়ে লগইন করুন। **Users** সেকশনে গিয়ে ডেমো অ্যাকাউন্টগুলোর পাসওয়ার্ড দ্রুত পরিবর্তন করে নিন।

---

## 6. SLA-breach ক্রোন জব সেটআপ করুন (Cron Job)

টিকিটের সময়সীমা ও নোটিফিকেশনের জন্য cPanel ➔ **Cron Jobs** এ গিয়ে প্রতি ৫ মিনিটে রান হওয়ার জন্য একটি ক্রোন জব যোগ করুন:

```bash
*/5 * * * * php /home/CPANELUSER/public_html/domains/subdomains/portal.visiontech.com.bd/artisan schedule:run >> /dev/null 2>&1
```
*(এখানে `CPANELUSER` এর জায়গায় আপনার আসল cPanel ইউজারনাম বসাবেন)*

---

## 7. ফাইল পারমিশন ভেরিফাই করুন (Permissions)

আপলোডের পর যদি 500 Error দেখায়:
- File Manager এ `storage/` এবং `bootstrap/cache/` ফোল্ডারের পারমিশন **755** (বা **775**) সেট করে দিন।

---

## সমস্য সমাধান (Troubleshooting)

- **সাদা পেজ / 500 Error:** `storage/logs/laravel.log` ফাইল ওপেন করে ভুলটি দেখুন।
- **CSS/JS লোড হচ্ছে না:** Document Root সঠিক আছে কি না (`public` ফোল্ডার ধরা আছে কি না) চেক করুন।
- **"could not find driver" DB Error:** cPanel PHP সেটিংস থেকে `pdo_mysql` এক্সটেনশন অন করুন (ধাপ ০)।
- **ছবি/ফাইল ৪০৪ Error:** `installer.php` আবার রান করুন যা ফাইলের ফিজিক্যাল কপি তৈরি করে দেবে।

---

## 8. WhatsApp ব্রিজিং ব্যাকগ্রাউন্ড সার্ভিস রান করা (cPanel Terminal)

`whatsapp_server.cjs` একটি ব্যাকগ্রাউন্ড সার্ভিস যা রিয়েল-টাইম হোয়াটসঅ্যাপ কানেকশন ধরে রাখে। cPanel **Terminal** ব্যবহার করে এটি ব্যাকগ্রাউন্ডে এবং ক্রোন ওয়াচডগ দিয়ে সার্বক্ষণিক চালু রাখতে পারবেন:

### 8.1 Node ভার্সন চেক করুন (Node ≥ 20 প্রয়োজন)

cPanel ➔ **Terminal** এ গিয়ে লিখুন:

```bash
node -v
```

যদি Node 20 এর কম হয় বা না থাকে, `nvm` আছে কি না দেখুন:

```bash
command -v nvm || ls -la ~/.nvm 2>/dev/null
```

`nvm` থাকলে Node 20 ইনস্টল করে নিন:

```bash
nvm install 20
nvm use 20
```

### 8.2 ডিপেন্ডেন্সি ইনস্টল করুন

```bash
cd ~/public_html/domains/subdomains/portal.visiontech.com.bd
npm install --omit=dev
```

### 8.3 সার্ভিস স্ক্রিপ্ট পারমিশন দিন এবং স্টার্ট করুন

```bash
cd ~/public_html/domains/subdomains/portal.visiontech.com.bd
chmod +x whatsapp-service/*.sh
./whatsapp-service/wa-start.sh
```

এটি `Started whatsapp_server.cjs (PID ...)` আউটপুট দেবে। সার্ভিসটি সঠিকভাবে চলছে কি না দেখতে:

```bash
tail -f whatsapp-service/wa.log
```
*(বন্ধ করতে `Ctrl+C` চাপুন — ব্যাকগ্রাউন্ডে সার্ভিস চালু থাকবে)*

### 8.4 ক্রোন ওয়াচডগ (Cron Watchdog) দিয়ে সবসময় চালু রাখা

cPanel ➔ **Cron Jobs** এ প্রতি ৫ মিনিটে একটি ওয়াচডগ জব সেট করুন:

```bash
*/5 * * * * /home/CPANELUSER/public_html/domains/subdomains/portal.visiontech.com.bd/whatsapp-service/wa-watchdog.sh
```
*(এখানে `CPANELUSER` এর জায়গায় আপনার cPanel ইউজারনাম দিন)*

### 8.5 টেস্ট ও ভেরিফাই করুন

লগইন অবস্থায় `https://portal.visiontech.com.bd/whatsapp` পেজে যান এবং কিউআর কোড (QR Code) স্ক্যান করে আপনার হোয়াটসঅ্যাপ কানেক্ট করুন!

### পরবতী সার্ভিস ম্যানেজমেন্ট কমান্ডসমূহ:

```bash
./whatsapp-service/wa-stop.sh     # সার্ভিস বন্ধ করতে
./whatsapp-service/wa-start.sh    # আবার স্টার্ট করতে
tail -f whatsapp-service/wa.log   # লাইভ লগ দেখতে
```

<?php
/**
 * ISP Ticket System - cPanel / Server Environment Checker
 * --------------------------------------------------------
 * Upload this file to your cPanel root / public_html and open in browser:
 * e.g., https://yourdomain.com/environment.php
 *
 * NOTE: Delete this file after completing environment setup for security!
 */

// Disable error display to prevent output corruption
error_reporting(E_ALL);
ini_set('display_errors', '0');

$results = [
    'php' => [],
    'extensions' => [],
    'permissions' => [],
    'files' => [],
    'ini' => [],
    'db' => null,
    'symlink' => null,
];

// 1. PHP Version Check (Laravel 11 requires PHP >= 8.2)
$minPhpVersion = '8.2.0';
$currentPhpVersion = PHP_VERSION;
$phpPass = version_compare($currentPhpVersion, $minPhpVersion, '>=');
$results['php'] = [
    'name' => 'PHP Version (>= ' . $minPhpVersion . ')',
    'current' => $currentPhpVersion,
    'pass' => $phpPass,
    'required' => 'PHP ' . $minPhpVersion . ' or higher'
];

// 2. Required Extensions Check
$requiredExtensions = [
    'bcmath' => 'Required for precision math calculations',
    'ctype' => 'Required for character type checking',
    'curl' => 'Required for external HTTP API requests',
    'dom' => 'Required for XML / HTML document processing',
    'fileinfo' => 'Required for file mime-type detection',
    'gd' => 'Required for image upload and avatar processing',
    'json' => 'Required for JSON data handling',
    'mbstring' => 'Required for multibyte string processing',
    'openssl' => 'Required for encryption and security',
    'pdo' => 'Required for database abstraction',
    'pdo_mysql' => 'Required for MySQL database connection',
    'tokenizer' => 'Required for PHP code tokenization',
    'xml' => 'Required for XML handling',
    'zip' => 'Required for archive handling',
];

foreach ($requiredExtensions as $ext => $reason) {
    $isLoaded = extension_loaded($ext);
    $results['extensions'][$ext] = [
        'name' => 'ext-' . $ext,
        'pass' => $isLoaded,
        'reason' => $reason,
        'required' => 'Installed & Enabled'
    ];
}

// 3. File & Directory Permissions
$baseDir = __DIR__;
$pathsToCheck = [
    'storage' => $baseDir . '/storage',
    'storage/framework' => $baseDir . '/storage/framework',
    'storage/logs' => $baseDir . '/storage/logs',
    'bootstrap/cache' => $baseDir . '/bootstrap/cache',
];

foreach ($pathsToCheck as $relPath => $fullPath) {
    $exists = file_exists($fullPath);
    $writable = $exists && is_writable($fullPath);
    $results['permissions'][$relPath] = [
        'name' => $relPath,
        'exists' => $exists,
        'pass' => $writable,
        'perms' => $exists ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'Missing',
        'required' => 'Writable (0775 / 0777)'
    ];
}

// 4. Essential Project Files
$essentialFiles = [
    '.env' => $baseDir . '/.env',
    'vendor/autoload.php' => $baseDir . '/vendor/autoload.php',
    'public/build/manifest.json' => $baseDir . '/public/build/manifest.json',
];

foreach ($essentialFiles as $relPath => $fullPath) {
    $exists = file_exists($fullPath);
    $results['files'][$relPath] = [
        'name' => $relPath,
        'pass' => $exists,
        'required' => 'File Must Exist'
    ];
}

// 5. php.ini Recommendations
function parseSize($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    }
    return round($size);
}

$iniChecks = [
    'upload_max_filesize' => ['min' => '10M', 'val' => ini_get('upload_max_filesize')],
    'post_max_size'       => ['min' => '12M', 'val' => ini_get('post_max_size')],
    'memory_limit'        => ['min' => '128M', 'val' => ini_get('memory_limit')],
    'max_execution_time'  => ['min' => '60',   'val' => ini_get('max_execution_time')],
];

foreach ($iniChecks as $setting => $data) {
    $currentBytes = parseSize($data['val']);
    $minBytes = parseSize($data['min']);
    $pass = ($currentBytes >= $minBytes) || ($data['val'] == -1);
    $results['ini'][$setting] = [
        'name' => $setting,
        'current' => $data['val'],
        'min' => $data['min'],
        'pass' => $pass
    ];
}

// 6. Database Connection Check (Read from .env)
$envPath = $baseDir . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_HOST=(.*)/', $envContent, $dbHost);
    preg_match('/DB_PORT=(.*)/', $envContent, $dbPort);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $dbName);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $dbUser);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $dbPass);

    $host = trim($dbHost[1] ?? '127.0.0.1', "\"' \r\n");
    $port = trim($dbPort[1] ?? '3306', "\"' \r\n");
    $name = trim($dbName[1] ?? '', "\"' \r\n");
    $user = trim($dbUser[1] ?? '', "\"' \r\n");
    $pass = trim($dbPass[1] ?? '', "\"' \r\n");

    if (!empty($name) && !empty($user)) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $results['db'] = [
                'pass' => true,
                'message' => "Successfully connected to MySQL database '{$name}' at {$host}:{$port}!"
            ];
        } catch (PDOException $e) {
            $results['db'] = [
                'pass' => false,
                'message' => "Database Connection Failed: " . $e->getMessage()
            ];
        }
    } else {
        $results['db'] = [
            'pass' => false,
            'message' => "DB_DATABASE or DB_USERNAME missing in .env file"
        ];
    }
} else {
    $results['db'] = [
        'pass' => false,
        'message' => ".env file not found. Create .env file first."
    ];
}

// 7. Storage Symlink Check
$publicStorage = $baseDir . '/public/storage';
$symlinkPass = is_link($publicStorage) || is_dir($publicStorage);
$results['symlink'] = [
    'pass' => $symlinkPass,
    'message' => $symlinkPass ? "public/storage link exists." : "public/storage link missing! Run 'php artisan storage:link'"
];

// Calculate overall score
$totalChecks = 1 + count($results['extensions']) + count($results['permissions']) + count($results['files']);
$passedChecks = ($results['php']['pass'] ? 1 : 0);
foreach ($results['extensions'] as $ext) { if ($ext['pass']) $passedChecks++; }
foreach ($results['permissions'] as $perm) { if ($perm['pass']) $passedChecks++; }
foreach ($results['files'] as $file) { if ($file['pass']) $passedChecks++; }

$allPassed = ($passedChecks === $totalChecks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Environment Checker - ISP Ticket System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚙️</span>
                    <div>
                        <h1 class="text-2xl font-extrabold text-white">cPanel / Server Environment Checker</h1>
                        <p class="text-slate-400 text-sm mt-0.5">ISP Ticket System (Laravel 11 Deployment Validator)</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-slate-900/80 px-4 py-2.5 rounded-xl border border-slate-700">
                <span class="text-sm font-semibold text-slate-300">Overall Readiness:</span>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider <?php echo $allPassed ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'; ?>">
                    <?php echo $passedChecks; ?> / <?php echo $totalChecks; ?> PASSED
                </span>
            </div>
        </div>

        <!-- Pre-Upload / Post-Upload Status Banner -->
        <?php 
            $projectFilesUploaded = file_exists($baseDir . '/artisan') || file_exists($baseDir . '/composer.json');
        ?>
        <?php if (!$projectFilesUploaded): ?>
        <div class="bg-indigo-950/60 border border-indigo-700/60 rounded-xl p-4 flex items-start gap-3 text-indigo-200 text-xs sm:text-sm shadow-md">
            <span class="text-xl">🚀</span>
            <div>
                <strong class="font-bold block text-white text-sm sm:text-base">Pre-Upload Server Check Mode</strong>
                You uploaded <code>environment.php</code> first to test your server. Below you can check if your server's <strong>PHP Version (>= 8.2)</strong>, <strong>Required Extensions</strong>, and <strong>php.ini limits</strong> are ready BEFORE uploading your project files or pulling from Git!
            </div>
        </div>
        <?php endif; ?>

        <!-- Security Warning Alert -->
        <div class="bg-rose-950/40 border border-rose-800/60 rounded-xl p-4 flex items-start gap-3 text-rose-200 text-xs sm:text-sm">
            <span class="text-lg">⚠️</span>
            <div>
                <strong class="font-bold block text-rose-100">Security Warning:</strong>
                Remember to <strong>delete environment.php</strong> from your server public directory once your environment check is complete!
            </div>
        </div>

        <!-- 1. PHP Version -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>🐘</span> PHP Version
            </h2>
            <div class="flex items-center justify-between p-4 rounded-xl <?php echo $results['php']['pass'] ? 'bg-emerald-950/30 border border-emerald-800/40 text-emerald-200' : 'bg-rose-950/30 border border-rose-800/40 text-rose-200'; ?>">
                <div>
                    <div class="font-semibold text-base"><?php echo $results['php']['name']; ?></div>
                    <div class="text-xs opacity-80">Current Version: <strong><?php echo $results['php']['current']; ?></strong></div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $results['php']['pass'] ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'; ?>">
                    <?php echo $results['php']['pass'] ? '✓ PASS' : '✗ REQUIRED'; ?>
                </span>
            </div>
        </div>

        <!-- 2. PHP Extensions -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>🧩</span> Required PHP Extensions (<?php echo count(array_filter($results['extensions'], fn($e) => $e['pass'])); ?> / <?php echo count($results['extensions']); ?>)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach ($results['extensions'] as $ext): ?>
                    <div class="flex items-center justify-between p-3.5 rounded-xl border transition-all <?php echo $ext['pass'] ? 'bg-slate-900/50 border-slate-700/60' : 'bg-rose-950/40 border-rose-800/60'; ?>">
                        <div>
                            <div class="font-mono font-bold text-sm <?php echo $ext['pass'] ? 'text-slate-200' : 'text-rose-300'; ?>">
                                <?php echo $ext['name']; ?>
                            </div>
                            <div class="text-[11px] text-slate-400"><?php echo $ext['reason']; ?></div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase flex-shrink-0 <?php echo $ext['pass'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500 text-white'; ?>">
                            <?php echo $ext['pass'] ? '✓ Active' : '✗ Missing'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. Folder Permissions -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>📁</span> Folder Writable Permissions
            </h2>
            <div class="space-y-3">
                <?php foreach ($results['permissions'] as $perm): ?>
                    <div class="flex items-center justify-between p-3.5 rounded-xl border <?php echo $perm['pass'] ? 'bg-slate-900/50 border-slate-700/60' : 'bg-rose-950/40 border-rose-800/60'; ?>">
                        <div>
                            <div class="font-mono font-semibold text-sm text-slate-200"><?php echo $perm['name']; ?></div>
                            <div class="text-[11px] text-slate-400">Current Permissions: <strong><?php echo $perm['perms']; ?></strong> | Required: <?php echo $perm['required']; ?></div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase <?php echo $perm['pass'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500 text-white'; ?>">
                            <?php echo $perm['pass'] ? '✓ Writable' : '✗ Fix Permission'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4. Essential Files -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>📄</span> Essential Files Checklist
            </h2>
            <div class="space-y-3">
                <?php foreach ($results['files'] as $file): ?>
                    <div class="flex items-center justify-between p-3.5 rounded-xl border <?php echo $file['pass'] ? 'bg-slate-900/50 border-slate-700/60' : 'bg-amber-950/40 border-amber-800/60'; ?>">
                        <div class="font-mono font-semibold text-sm text-slate-200"><?php echo $file['name']; ?></div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase <?php echo $file['pass'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500 text-slate-950'; ?>">
                            <?php echo $file['pass'] ? '✓ Found' : '⚠️ Missing File'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 5. php.ini Settings -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>⚙️</span> Recommended php.ini Directives
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($results['ini'] as $ini): ?>
                    <div class="p-3.5 rounded-xl border bg-slate-900/50 border-slate-700/60 flex items-center justify-between">
                        <div>
                            <div class="font-mono font-semibold text-xs text-slate-300"><?php echo $ini['name']; ?></div>
                            <div class="text-[11px] text-slate-400">Current: <strong><?php echo $ini['current']; ?></strong> (Recommended >= <?php echo $ini['min']; ?>)</div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase <?php echo $ini['pass'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'; ?>">
                            <?php echo $ini['pass'] ? '✓ Good' : '⚠️ Low'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 6. Database Connection -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                <span>🛢️</span> Database Connection Test
            </h2>
            <div class="p-4 rounded-xl border <?php echo $results['db']['pass'] ? 'bg-emerald-950/30 border-emerald-800/40 text-emerald-200' : 'bg-rose-950/30 border-rose-800/40 text-rose-200'; ?>">
                <div class="font-semibold text-sm mb-1"><?php echo $results['db']['pass'] ? '✓ Connection Successful' : '✗ Database Error'; ?></div>
                <div class="text-xs font-mono opacity-90"><?php echo htmlspecialchars($results['db']['message']); ?></div>
            </div>
        </div>

        <!-- 7. Storage Symlink -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                <span>🔗</span> Public Storage Symlink
            </h2>
            <div class="p-4 rounded-xl border <?php echo $results['symlink']['pass'] ? 'bg-emerald-950/30 border-emerald-800/40 text-emerald-200' : 'bg-amber-950/30 border-amber-800/40 text-amber-200'; ?>">
                <div class="font-semibold text-sm mb-1"><?php echo $results['symlink']['pass'] ? '✓ Storage Symlink OK' : '⚠️ Storage Symlink Missing'; ?></div>
                <div class="text-xs opacity-90"><?php echo htmlspecialchars($results['symlink']['message']); ?></div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 pt-4">
            ISP Ticket System Auto Environment Diagnostic Tool &bull; Powered by PHP <?php echo PHP_VERSION; ?>
        </div>

    </div>
</body>
</html>

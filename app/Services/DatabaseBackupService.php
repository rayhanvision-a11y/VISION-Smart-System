<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupService
{
    /**
     * Get directory path where backup files are stored.
     */
    public static function getBackupDir(): string
    {
        $dir = storage_path('app/backups');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Create a new database backup SQL file and prune old backups.
     */
    public function createBackup(string $type = 'manual'): array
    {
        $dir = self::getBackupDir();
        $prefix = ($type === 'auto') ? 'backup_auto_' : 'backup_manual_';
        $filename = $prefix.now()->format('Y-m-d_H-i-s').'.sql';
        $filepath = $dir.DIRECTORY_SEPARATOR.$filename;

        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $sql = "-- ISP Tickets Database Backup\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n";
        $sql .= "-- Database: {$dbName}\n\n";
        $sql .= "SET foreign_key_checks = 0;\n\n";

        // Get list of tables
        $tables = DB::select('SHOW TABLES');
        $dbKey = 'Tables_in_'.$dbName;

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$dbKey ?? current((array) $tableObj);

            // Table structure
            $sql .= "-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

            $createRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (! empty($createRes)) {
                $createArr = (array) $createRes[0];
                $createSql = $createArr['Create Table'] ?? reset($createArr);
                $sql .= $createSql.";\n\n";
            }

            // Table data
            $sql .= "-- Dumping data for table `{$tableName}`\n";
            $rows = DB::table($tableName)->get();

            if ($rows->count() > 0) {
                $columnNames = array_keys((array) $rows->first());
                $escapedColumns = array_map(fn ($col) => "`{$col}`", $columnNames);

                $sql .= "INSERT INTO `{$tableName}` (".implode(', ', $escapedColumns).") VALUES\n";

                $rowStrings = [];
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $values = [];
                    foreach ($rowArray as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($val) && ! is_string($val)) {
                            $values[] = $val;
                        } else {
                            $values[] = $pdo->quote((string) $val);
                        }
                    }
                    $rowStrings[] = '('.implode(', ', $values).')';
                }

                $sql .= implode(",\n", $rowStrings).";\n\n";
            }
        }

        $sql .= "SET foreign_key_checks = 1;\n";

        File::put($filepath, $sql);

        // Prune old backups (Keep 30 days max / max 30 files)
        $this->pruneOldBackups();

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => File::size($filepath),
        ];
    }

    /**
     * Restore database from a given backup filename or raw SQL string.
     */
    public function restoreBackup(string $filepath): bool
    {
        if (! File::exists($filepath)) {
            return false;
        }

        $sql = File::get($filepath);
        DB::unprepared($sql);

        return true;
    }

    /**
     * List all stored backup files sorted by creation date descending.
     */
    public function listBackups(): array
    {
        $dir = self::getBackupDir();
        $files = File::files($dir);

        $backups = [];
        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['sql', 'gz'])) {
                $isAuto = str_contains($file->getFilename(), '_auto_');
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'filepath' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'human_size' => $this->humanSize($file->getSize()),
                    'type' => $isAuto ? 'auto' : 'manual',
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        // Sort newest first
        usort($backups, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Prune backups older than 30 days or beyond max 30 count (FIFO rule).
     */
    public function pruneOldBackups(int $maxFiles = 30, int $maxDays = 30): int
    {
        $backups = $this->listBackups(); // newest first
        $deleted = 0;
        $thirtyDaysAgo = now()->subDays($maxDays)->timestamp;

        foreach ($backups as $index => $backup) {
            // Delete if older than 30 days OR if count exceeds max 30 files
            $isTooOld = $backup['created_at']->timestamp < $thirtyDaysAgo;
            $isBeyondLimit = $index >= $maxFiles;

            if ($isTooOld || $isBeyondLimit) {
                if (File::exists($backup['filepath'])) {
                    File::delete($backup['filepath']);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Convert bytes to human readable size.
     */
    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-database {--keep=14 : Number of daily backups to retain}';

    protected $description = 'Dump the MySQL database to storage/app/backups as a gzipped SQL file';

    public function handle(): int
    {
        $connection = config('database.default');
        if ($connection !== 'mysql') {
            $this->error("Only mysql connection is supported (got: {$connection}).");

            return self::FAILURE;
        }

        $cfg = config('database.connections.mysql');
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Ymd_His');
        $file = "{$dir}/db_{$cfg['database']}_{$stamp}.sql.gz";

        $cmd = sprintf(
            'mysqldump --single-transaction --quick --routines --triggers -h%s -P%s -u%s %s %s | gzip > %s',
            escapeshellarg($cfg['host']),
            escapeshellarg((string) $cfg['port']),
            escapeshellarg($cfg['username']),
            $cfg['password'] !== '' ? '-p'.escapeshellarg($cfg['password']) : '',
            escapeshellarg($cfg['database']),
            escapeshellarg($file)
        );

        $this->info("Writing {$file}");
        $exit = 0;
        passthru($cmd, $exit);

        if ($exit !== 0 || ! file_exists($file)) {
            $this->error("mysqldump failed with exit code {$exit}.");

            return self::FAILURE;
        }

        $this->pruneOld($dir, (int) $this->option('keep'));

        $this->info('Backup complete: '.round(filesize($file) / 1024, 1).' KB');

        return self::SUCCESS;
    }

    private function pruneOld(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql.gz'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        foreach ($files->slice($keep) as $old) {
            @unlink($old->getPathname());
            $this->line("Pruned: {$old->getFilename()}");
        }
    }
}

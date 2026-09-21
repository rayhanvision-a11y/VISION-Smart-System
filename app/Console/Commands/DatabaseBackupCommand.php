<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a daily database backup SQL dump and prune backups older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Starting database backup...');

        try {
            $result = $backupService->createBackup('auto');
            $this->info("Backup created successfully: {$result['filename']} (" . round($result['size'] / 1024, 2) . " KB)");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

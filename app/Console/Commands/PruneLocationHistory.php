<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneLocationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-location-history {--days=7 : Number of days of GPS history to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old technician GPS location history to keep database size lean';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = DB::table('user_location_history')
            ->where('recorded_at', '<', $cutoff)
            ->delete();

        $this->info("Successfully pruned {$deleted} GPS location history records older than {$days} days.");

        return Command::SUCCESS;
    }
}

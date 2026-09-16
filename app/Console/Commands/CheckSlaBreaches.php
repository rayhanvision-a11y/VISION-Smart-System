<?php

namespace App\Console\Commands;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-sla-breaches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify assignees and admins about tickets that have breached their SLA (due_at)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $breached = Ticket::whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNull('sla_notified_at')
            ->whereNotIn('status', ['resolved'])
            ->get();

        if ($breached->isEmpty()) {
            $this->info('No new SLA breaches.');
            return self::SUCCESS;
        }

        $adminIds = User::whereIn('role', ['admin', 'super_admin'])->pluck('id')->all();
        $policies = SlaPolicy::all()->keyBy('priority');

        $notified = 0;
        foreach ($breached as $ticket) {
            $policy = $policies->get($ticket->priority);
            // Default to escalating when no policy is configured for this priority yet.
            $shouldEscalate = $policy ? $policy->escalate_on_breach : true;

            if (!$shouldEscalate) {
                $ticket->update(['sla_notified_at' => now()]);
                continue;
            }

            $message = "SLA breached: Ticket #{$ticket->id} \"{$ticket->title}\" was due {$ticket->due_at->diffForHumans()}.";

            $recipientIds = $adminIds;
            if ($ticket->assigned_to) {
                $recipientIds[] = $ticket->assigned_to;
            }

            NotificationService::sendToMany($recipientIds, $message, $ticket->id);

            $ticket->update(['sla_notified_at' => now()]);
            $notified++;
        }

        $this->info("Sent SLA breach alerts for {$notified} of {$breached->count()} breached ticket(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\GoogleSheetSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncTicketToGoogleSheet implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;
    public int $backoff = 10;

    public function __construct(
        public int $ticketId,
        public string $action,
        public ?string $remarks = null,
    ) {}

    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (! $ticket) {
            return;
        }

        if ($this->action === 'create') {
            GoogleSheetSyncService::syncTicketCreated($ticket);
        } else {
            GoogleSheetSyncService::syncTicketUpdated($ticket, $this->remarks);
        }
    }
}

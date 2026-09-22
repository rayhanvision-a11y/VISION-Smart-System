<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addIndex = function ($table, $columns, $name) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                    $t->index($columns, $name);
                });
            } catch (Throwable $e) {
                // Index already exists or skipped
            }
        };

        $addIndex('tickets', 'due_at', 'idx_tickets_due_at');
        $addIndex('tickets', 'resolved_at', 'idx_tickets_resolved_at');
        $addIndex('tickets', 'category', 'idx_tickets_category');
        $addIndex('tickets', ['assigned_to', 'status'], 'idx_tickets_assigned_status');
        $addIndex('tickets', ['created_by', 'status'], 'idx_tickets_created_status');

        $addIndex('ticket_history', 'ticket_id', 'idx_th_ticket_id');
        $addIndex('ticket_history', 'changed_by', 'idx_th_changed_by');
    }

    public function down(): void
    {
        $dropIndex = function ($table, $name) {
            try {
                Schema::table($table, function (Blueprint $t) use ($name) {
                    $t->dropIndex($name);
                });
            } catch (Throwable $e) {
                // Ignore
            }
        };

        $dropIndex('tickets', 'idx_tickets_due_at');
        $dropIndex('tickets', 'idx_tickets_resolved_at');
        $dropIndex('tickets', 'idx_tickets_category');
        $dropIndex('tickets', 'idx_tickets_assigned_status');
        $dropIndex('tickets', 'idx_tickets_created_status');

        $dropIndex('ticket_history', 'idx_th_ticket_id');
        $dropIndex('ticket_history', 'idx_th_changed_by');
    }
};

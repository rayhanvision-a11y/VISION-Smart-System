<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tickets indexes
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->index('status', 'idx_tickets_status');
            } catch (Exception $e) {
            }
            try {
                $table->index('priority', 'idx_tickets_priority');
            } catch (Exception $e) {
            }
            try {
                $table->index('assigned_to', 'idx_tickets_assigned_to');
            } catch (Exception $e) {
            }
            try {
                $table->index('created_at', 'idx_tickets_created_at');
            } catch (Exception $e) {
            }
            try {
                $table->index('created_by', 'idx_tickets_created_by');
            } catch (Exception $e) {
            }
        });

        // notifications indexes
        Schema::table('notifications', function (Blueprint $table) {
            try {
                $table->index('user_id', 'idx_notifications_user_id');
            } catch (Exception $e) {
            }
            try {
                $table->index('is_read', 'idx_notifications_is_read');
            } catch (Exception $e) {
            }
            try {
                $table->index(['user_id', 'is_read'], 'idx_notifications_user_is_read');
            } catch (Exception $e) {
            }
        });

        // ticket_messages indexes
        Schema::table('ticket_messages', function (Blueprint $table) {
            try {
                $table->index('ticket_id', 'idx_ticket_messages_ticket_id');
            } catch (Exception $e) {
            }
            try {
                $table->index('sender_id', 'idx_ticket_messages_sender_id');
            } catch (Exception $e) {
            }
        });

        // ticket_message_reactions indexes
        Schema::table('ticket_message_reactions', function (Blueprint $table) {
            try {
                $table->index('ticket_message_id', 'idx_tmr_ticket_message_id');
            } catch (Exception $e) {
            }
            try {
                $table->index('user_id', 'idx_tmr_user_id');
            } catch (Exception $e) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_tickets_status');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_tickets_priority');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_tickets_assigned_to');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_tickets_created_at');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_tickets_created_by');
            } catch (Exception $e) {
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_notifications_user_id');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_notifications_is_read');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_notifications_user_is_read');
            } catch (Exception $e) {
            }
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_ticket_messages_ticket_id');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_ticket_messages_sender_id');
            } catch (Exception $e) {
            }
        });

        Schema::table('ticket_message_reactions', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_tmr_ticket_message_id');
            } catch (Exception $e) {
            }
            try {
                $table->dropIndex('idx_tmr_user_id');
            } catch (Exception $e) {
            }
        });
    }
};

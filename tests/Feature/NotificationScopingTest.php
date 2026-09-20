<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_ticket_creation_only_notifies_assignee_and_super_admin()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $creator = User::factory()->create(['role' => 'admin']);
        $assignedNoc = User::factory()->create(['role' => 'noc']);
        $otherNoc = User::factory()->create(['role' => 'noc']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $reseller = User::factory()->create(['role' => 'reseller']);

        $response = $this->actingAs($creator)->post('/tickets', [
            'title'       => 'Assigned Ticket Test',
            'description' => 'Testing notification scoping on assigned ticket',
            'category'    => 'Fiber Issue',
            'priority'    => 'medium',
            'assigned_to' => $assignedNoc->id,
        ]);

        $response->assertRedirect();
        $ticket = Ticket::where('title', 'Assigned Ticket Test')->firstOrFail();

        // Super Admin should have received notification
        $this->assertDatabaseHas('notifications', [
            'user_id'   => $superAdmin->id,
            'ticket_id' => $ticket->id,
        ]);

        // Assigned NOC should have received notification
        $this->assertDatabaseHas('notifications', [
            'user_id'   => $assignedNoc->id,
            'ticket_id' => $ticket->id,
        ]);

        // Other NOC must NOT have received notification
        $this->assertDatabaseMissing('notifications', [
            'user_id'   => $otherNoc->id,
            'ticket_id' => $ticket->id,
        ]);

        // Other Admin must NOT have received notification
        $this->assertDatabaseMissing('notifications', [
            'user_id'   => $otherAdmin->id,
            'ticket_id' => $ticket->id,
        ]);

        // Reseller must NOT have received notification
        $this->assertDatabaseMissing('notifications', [
            'user_id'   => $reseller->id,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_reseller_unassigned_ticket_does_not_notify_call_center()
    {
        $reseller = User::factory()->create(['role' => 'reseller']);
        $callCenter = User::factory()->create(['role' => 'call_center']);
        $noc = User::factory()->create(['role' => 'noc']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($reseller)->post('/tickets', [
            'title'       => 'Reseller Customer Outage',
            'description' => 'Link is down',
            'category'    => 'Link Down',
            'priority'    => 'high',
        ]);

        $response->assertRedirect();
        $ticket = Ticket::where('title', 'Reseller Customer Outage')->firstOrFail();

        // Call Center cannot see reseller tickets, so they must NOT get a notification
        $this->assertDatabaseMissing('notifications', [
            'user_id'   => $callCenter->id,
            'ticket_id' => $ticket->id,
        ]);

        // NOC and Super Admin can see unassigned reseller tickets
        $this->assertDatabaseHas('notifications', [
            'user_id'   => $noc->id,
            'ticket_id' => $ticket->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id'   => $superAdmin->id,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_notification_controller_scopes_unread_count_and_dropdown()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $nocBob = User::factory()->create(['role' => 'noc']);
        $nocAlice = User::factory()->create(['role' => 'noc']);

        // Ticket assigned to Bob
        $ticket = Ticket::create([
            'ticket_key'  => '260920555',
            'title'       => 'Bob Ticket',
            'description' => 'Only for bob and superadmin',
            'category'    => 'Fiber',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $superAdmin->id,
            'assigned_to' => $nocBob->id,
        ]);

        // Suppose a legacy or rogue notification exists in DB for Alice
        Notification::create([
            'user_id'   => $nocAlice->id,
            'ticket_id' => $ticket->id,
            'message'   => 'Secret ticket notification',
            'is_read'   => false,
        ]);

        // Alice cannot see Bob's ticket
        $this->assertFalse(Ticket::where('id', $ticket->id)->forUser($nocAlice)->exists());

        // Calling unread-count as Alice should return count 0
        $response = $this->actingAs($nocAlice)->getJson('/notifications/unread-count');
        $response->assertOk();
        $response->assertJson(['count' => 0]);

        // Dropdown for Alice should say "No notifications yet."
        $response = $this->actingAs($nocAlice)->get('/notifications/dropdown');
        $response->assertOk();
        $response->assertSee('No notifications yet.');
        $response->assertDontSee('Secret ticket notification');

        // But for SuperAdmin, if a notification is created for them, it appears
        Notification::create([
            'user_id'   => $superAdmin->id,
            'ticket_id' => $ticket->id,
            'message'   => 'Super admin notification',
            'is_read'   => false,
        ]);

        $response = $this->actingAs($superAdmin)->getJson('/notifications/unread-count');
        $response->assertOk();
        $response->assertJson(['count' => 1]);
    }

    public function test_private_message_does_not_notify_reseller()
    {
        $reseller = User::factory()->create(['role' => 'reseller']);
        $noc = User::factory()->create(['role' => 'noc']);

        // Ticket created by reseller
        $ticket = Ticket::create([
            'ticket_key'  => '260920556',
            'title'       => 'Reseller Ticket for Private Chat',
            'description' => 'Test private msg',
            'category'    => 'Fiber',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $reseller->id,
            'assigned_to' => $noc->id,
        ]);

        // NOC posts an internal private message
        $response = $this->actingAs($noc)->postJson("/tickets/{$ticket->id}/messages", [
            'message'    => 'Internal NOC investigation note',
            'is_private' => 1,
        ]);

        $response->assertOk();

        // Reseller should NOT receive any notification about this private note
        $this->assertDatabaseMissing('notifications', [
            'user_id'   => $reseller->id,
            'ticket_id' => $ticket->id,
        ]);
    }
}

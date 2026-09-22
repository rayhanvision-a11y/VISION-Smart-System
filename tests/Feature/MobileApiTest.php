<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_mobile_api(): void
    {
        $user = User::factory()->create([
            'email' => 'mobileuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'mobileuser@example.com',
            'password' => 'password123',
            'device_name' => 'flutter_app',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_user_can_update_fcm_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/fcm-token', [
                'fcm_token' => 'sample_fcm_token_12345',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'sample_fcm_token_12345',
        ]);
    }

    public function test_user_can_fetch_scoped_tickets_via_api(): void
    {
        $user = User::factory()->create(['role' => 'noc']);

        $ticket = Ticket::create([
            'ticket_key' => '260922001',
            'title' => 'Fiber Link Down',
            'description' => 'Main pop link down',
            'priority' => 'high',
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Fiber Link Down']);
    }

    public function test_user_can_create_ticket_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tickets', [
                'title' => 'New Router Configuration',
                'description' => 'Need BGP setup',
                'priority' => 'medium',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Router Configuration']);

        $this->assertDatabaseHas('tickets', [
            'title' => 'New Router Configuration',
            'created_by' => $user->id,
        ]);
    }

    public function test_user_can_update_ticket_status_via_api(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $ticket = Ticket::create([
            'ticket_key' => '260922002',
            'title' => 'Billing Issue',
            'description' => 'Invoice query',
            'priority' => 'low',
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/status", [
                'status' => 'resolved',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'resolved']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    public function test_user_can_add_reply_message_via_api(): void
    {
        $user = User::factory()->create(['role' => 'noc']);

        $ticket = Ticket::create([
            'ticket_key' => '260922003',
            'title' => 'Speed Issue',
            'description' => 'Slow connection',
            'priority' => 'medium',
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/messages", [
                'message' => 'Checked signal levels, SNR is fine.',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Checked signal levels, SNR is fine.']);

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'sender_id' => $user->id,
            'message' => 'Checked signal levels, SNR is fine.',
        ]);
    }

    public function test_firebase_service_handles_disabled_state_gracefully(): void
    {
        config(['firebase.enabled' => false]);
        $firebase = app(FirebaseService::class);

        $this->assertFalse($firebase->isEnabled());

        $user = User::factory()->create();
        $ticket = Ticket::create([
            'ticket_key' => '260922004',
            'title' => 'Test Ticket',
            'description' => 'Desc',
            'priority' => 'low',
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $this->assertFalse($firebase->syncTicket($ticket));
    }
}

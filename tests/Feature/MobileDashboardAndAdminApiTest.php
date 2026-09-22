<?php

namespace Tests\Feature;

use App\Models\PopOffice;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileDashboardAndAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_dashboard_stats(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'team' => 'IT Team']);

        Ticket::create([
            'ticket_key' => '260922010',
            'title' => 'Server Outage',
            'description' => 'Core switch down',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role', 'team'],
                'stats' => ['total', 'in_progress', 'pending', 'waiting_for_customer_feedback', 'resolved', 'urgent', 'overdue'],
                'duty_teams',
                'recent_tickets',
            ])
            ->assertJsonPath('stats.in_progress', 1)
            ->assertJsonPath('stats.total', 1);
    }

    public function test_authenticated_user_can_fetch_roster(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'NOC Engineer 1', 'role' => 'noc', 'team' => 'NOC team', 'current_shift' => 'day_shift']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/roster');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'roster' => [
                    '*' => ['team_key', 'team_label', 'total', 'on_duty_count', 'members'],
                ],
            ]);
    }

    public function test_admin_can_fetch_users_directory_but_reseller_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reseller = User::factory()->create(['role' => 'reseller']);

        $resAdmin = $this->actingAs($admin, 'sanctum')->getJson('/api/users');
        $resAdmin->assertStatus(200);

        $resReseller = $this->actingAs($reseller, 'sanctum')->getJson('/api/users');
        $resReseller->assertStatus(403);
    }

    public function test_admin_can_assign_ticket_and_update_priority(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'noc', 'team' => 'NOC team']);

        $ticket = Ticket::create([
            'ticket_key' => '260922011',
            'title' => 'Latency Issue',
            'description' => 'High latency to gateway',
            'priority' => 'low',
            'status' => 'in_progress',
            'created_by' => $admin->id,
        ]);

        // Assign ticket
        $assignResponse = $this->actingAs($admin, 'sanctum')->postJson("/api/tickets/{$ticket->id}/assign", [
            'assigned_to' => $staff->id,
        ]);
        $assignResponse->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'assigned_to' => $staff->id]);

        // Update priority
        $prioResponse = $this->actingAs($admin, 'sanctum')->postJson("/api/tickets/{$ticket->id}/priority", [
            'priority' => 'urgent',
        ]);
        $prioResponse->assertStatus(200);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'priority' => 'urgent']);
    }

    public function test_metadata_endpoints_return_active_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        TicketCategory::create(['name' => 'Optical Fiber', 'slug' => 'optical-fiber', 'is_active' => true]);
        PopOffice::create(['name' => 'Dhanmondi POP', 'is_active' => true]);

        $resCat = $this->actingAs($user, 'sanctum')->getJson('/api/categories');
        $resCat->assertStatus(200)->assertJsonStructure(['categories']);

        $resPop = $this->actingAs($user, 'sanctum')->getJson('/api/pop-offices');
        $resPop->assertStatus(200)->assertJsonStructure(['pop_offices']);

        $resStaff = $this->actingAs($user, 'sanctum')->getJson('/api/staff');
        $resStaff->assertStatus(200)->assertJsonStructure(['staff']);
    }
}

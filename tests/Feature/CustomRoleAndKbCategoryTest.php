<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\KbCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomRoleAndKbCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_kb_categories()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create category
        $response = $this->actingAs($admin)->post('/knowledge-base/categories', [
            'name'        => 'Test Network Cat',
            'description' => 'Network guides',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kb_categories', ['name' => 'Test Network Cat']);

        $category = KbCategory::where('name', 'Test Network Cat')->first();

        // Update category
        $response = $this->actingAs($admin)->put("/knowledge-base/categories/{$category->id}", [
            'name'        => 'Updated Network Cat',
            'description' => 'Updated desc',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kb_categories', ['name' => 'Updated Network Cat']);

        // Delete category
        $response = $this->actingAs($admin)->delete("/knowledge-base/categories/{$category->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('kb_categories', ['id' => $category->id]);
    }

    public function test_admin_can_crud_ticket_categories()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create ticket category
        $response = $this->actingAs($admin)->post('/ticket-categories', [
            'name'        => 'ONU Fiber Issue',
            'description' => 'Issues with optical network units',
            'color'       => '#10b981',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_categories', ['name' => 'ONU Fiber Issue']);

        $category = \App\Models\TicketCategory::where('name', 'ONU Fiber Issue')->first();

        // Update ticket category
        $response = $this->actingAs($admin)->put("/ticket-categories/{$category->id}", [
            'name'        => 'Updated ONU Fiber Issue',
            'description' => 'Updated details',
            'color'       => '#ef4444',
            'is_active'   => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_categories', ['name' => 'Updated ONU Fiber Issue']);

        // Delete ticket category
        $response = $this->actingAs($admin)->delete("/ticket-categories/{$category->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('ticket_categories', ['id' => $category->id]);
    }

    public function test_call_center_role_cannot_see_reseller_tickets()
    {
        $reseller = User::factory()->create(['role' => 'reseller']);
        $callCenter = User::factory()->create(['role' => 'call_center']);

        $resellerTicket = Ticket::create([
            'ticket_key'  => '260920001',
            'title'       => 'Reseller Issue',
            'description' => 'Help',
            'category'    => 'billing',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $reseller->id,
        ]);

        $visibleTickets = Ticket::forUser($callCenter)->get();
        $this->assertFalse($visibleTickets->contains($resellerTicket));
    }

    public function test_personal_assigned_ticket_visibility_scoping()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin      = User::factory()->create(['role' => 'admin']);
        $noc1       = User::factory()->create(['role' => 'noc']);
        $noc2       = User::factory()->create(['role' => 'noc']);

        // Admin assigns a ticket specifically to NOC1
        $assignedTicket = Ticket::create([
            'ticket_key'  => '260920002',
            'title'       => 'Secret NOC1 Task',
            'description' => 'Personal assignment',
            'category'    => 'line_fault',
            'priority'    => 'high',
            'status'      => 'in_progress',
            'created_by'  => $admin->id,
            'assigned_to' => $noc1->id,
        ]);

        // Super Admin can see it
        $this->assertTrue(Ticket::forUser($superAdmin)->get()->contains($assignedTicket));

        // Admin (creator) can see it
        $this->assertTrue(Ticket::forUser($admin)->get()->contains($assignedTicket));

        // NOC1 (assignee) can see it
        $this->assertTrue(Ticket::forUser($noc1)->get()->contains($assignedTicket));

        // NOC2 (unassigned third party) CANNOT see it
        $this->assertFalse(Ticket::forUser($noc2)->get()->contains($assignedTicket));
    }

    public function test_supervisor_roles_can_see_call_center_tickets()
    {
        $callCenter = User::factory()->create(['role' => 'call_center']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $seniorSupervisor = User::factory()->create(['role' => 'senior_supervisor']);

        $callCenterTicket = Ticket::create([
            'ticket_key'  => '260920003',
            'title'       => 'Call Center Customer Issue',
            'description' => 'Help needed',
            'category'    => 'billing',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $callCenter->id,
        ]);

        $this->assertTrue(Ticket::forUser($supervisor)->get()->contains($callCenterTicket));
        $this->assertTrue(Ticket::forUser($seniorSupervisor)->get()->contains($callCenterTicket));
    }

    public function test_noc_can_see_reseller_and_call_center_tickets()
    {
        $reseller   = User::factory()->create(['role' => 'reseller']);
        $callCenter = User::factory()->create(['role' => 'call_center']);
        $noc        = User::factory()->create(['role' => 'noc']);

        $resellerTicket = Ticket::create([
            'ticket_key'  => '260920004',
            'title'       => 'Reseller Line Issue',
            'description' => 'Fix line',
            'category'    => 'line_fault',
            'priority'    => 'high',
            'status'      => 'in_progress',
            'created_by'  => $reseller->id,
        ]);

        $callCenterTicket = Ticket::create([
            'ticket_key'  => '260920005',
            'title'       => 'Call Center Router Issue',
            'description' => 'Fix router',
            'category'    => 'router_issue',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'created_by'  => $callCenter->id,
        ]);

        $nocTickets = Ticket::forUser($noc)->get();
        $this->assertTrue($nocTickets->contains($resellerTicket));
        $this->assertTrue($nocTickets->contains($callCenterTicket));
    }
}

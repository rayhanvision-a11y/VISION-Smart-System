<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_attendance_monitoring_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('Attendance Logs & Biometric Monitoring');
    }

    public function test_reseller_cannot_access_attendance_monitoring_page(): void
    {
        $reseller = User::factory()->create(['role' => 'reseller']);

        $response = $this->actingAs($reseller)->get('/attendance');
        $response->assertStatus(403);
    }

    public function test_attendance_page_displays_logged_punches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['name' => 'Kabila Technician', 'office_id' => 'EMP-77']);

        AttendanceLog::create([
            'user_id'        => $staff->id,
            'employee_no'    => 'EMP-77',
            'person_name'    => 'Kabila Technician',
            'door_name'      => 'Gate 1',
            'event_type'     => 'face_match',
            'event_time'     => now(),
            'shift_assigned' => 'day_shift',
        ]);

        $response = $this->actingAs($admin)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('Kabila Technician');
        $response->assertSee('Gate 1');
        $response->assertSee('Day Shift');
    }

    public function test_admin_can_delete_attendance_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $log = AttendanceLog::create([
            'employee_no' => '999',
            'event_time'  => now(),
        ]);

        $response = $this->actingAs($admin)->delete("/attendance/{$log->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('attendance_logs', ['id' => $log->id]);
    }
}

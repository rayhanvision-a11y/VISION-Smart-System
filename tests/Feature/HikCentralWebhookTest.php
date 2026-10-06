<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HikCentralWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_hikcentral_status_endpoint_returns_online(): void
    {
        $response = $this->getJson('/api/hikcentral/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
            ]);
    }

    public function test_hikcentral_event_marks_matching_staff_on_duty_and_logs(): void
    {
        $user = User::factory()->create([
            'name'          => 'Rayhan Ahmed',
            'office_id'     => 'VT-101',
            'current_shift' => 'unassigned',
            'shift_date'    => null,
        ]);

        $payload = [
            'method' => 'OnEvent',
            'params' => [
                'ability' => 'event_acs',
                'events'  => [
                    [
                        'eventId'   => 'evt-001',
                        'eventType' => 196893,
                        'eventTime' => now()->format('Y-m-d') . 'T09:30:00+06:00',
                        'data'      => [
                            'extEventPersonNo' => 'VT-101',
                            'personName'       => 'Rayhan Ahmed',
                            'doorName'         => 'Main Office Gate',
                            'devName'          => 'Face Terminal Entrance',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/hikcentral/event', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'code' => '0',
                'msg'  => 'success',
                'data' => [
                    'total_processed' => 1,
                    'users_matched'   => 1,
                ],
            ]);

        $user->refresh();
        $this->assertEquals('day_shift', $user->current_shift);
        $this->assertEquals(now()->format('Y-m-d'), $user->shift_date);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'        => $user->id,
            'employee_no'    => 'VT-101',
            'person_name'    => 'Rayhan Ahmed',
            'shift_assigned' => 'day_shift',
            'door_name'      => 'Main Office Gate',
        ]);
    }

    public function test_hikcentral_event_handles_night_shift_after_2pm(): void
    {
        $user = User::factory()->create([
            'office_id'     => '102',
            'current_shift' => 'unassigned',
        ]);

        $payload = [
            'params' => [
                'events' => [
                    [
                        'eventTime' => now()->format('Y-m-d') . 'T15:15:00+06:00',
                        'data'      => [
                            'personId'   => '102',
                            'personName' => 'NOC Engineer',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/hikcentral/event', $payload);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('night_shift', $user->current_shift);
    }
}

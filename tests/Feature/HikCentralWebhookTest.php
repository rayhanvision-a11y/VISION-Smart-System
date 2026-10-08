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
        $this->assertEquals('1st_shift', $user->current_shift);
        $this->assertEquals(now()->format('Y-m-d'), $user->shift_date);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'        => $user->id,
            'employee_no'    => 'VT-101',
            'person_name'    => 'Rayhan Ahmed',
            'shift_assigned' => '1st_shift',
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
        $this->assertEquals('2nd_shift', $user->current_shift);
    }

    public function test_hikcentral_event_exits_1st_shift_after_6pm(): void
    {
        $user = User::factory()->create([
            'office_id'     => '105',
            'current_shift' => '1st_shift',
            'shift_date'    => now()->toDateString(),
        ]);

        $payload = [
            'params' => [
                'events' => [
                    [
                        'eventTime' => now()->format('Y-m-d') . 'T18:15:00+06:00',
                        'data'      => [
                            'personId'   => '105',
                            'personName' => 'Outgoing Staff',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/hikcentral/event', $payload);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('off_duty', $user->current_shift);
    }

    public function test_hikcentral_event_exits_2nd_shift_after_10pm(): void
    {
        $user = User::factory()->create([
            'office_id'     => '108',
            'current_shift' => '2nd_shift',
            'shift_date'    => now()->toDateString(),
        ]);

        $payload = [
            'params' => [
                'events' => [
                    [
                        'eventTime' => now()->format('Y-m-d') . 'T22:15:00+06:00',
                        'data'      => [
                            'personId'   => '108',
                            'personName' => 'Night Staff',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/hikcentral/event', $payload);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('off_duty', $user->current_shift);
    }

    public function test_hikcentral_event_saves_events_within_7_days_and_drops_older_backlog(): void
    {
        $user = User::factory()->create([
            'office_id'     => '106',
            'current_shift' => 'unassigned',
            'shift_date'    => null,
        ]);

        $payload = [
            'params' => [
                'events' => [
                    // Event within last 7 days (e.g. 3 days ago)
                    [
                        'eventTime' => now()->subDays(3)->setTime(8, 30, 0)->toIso8601String(),
                        'data'      => [
                            'personId'   => '106',
                            'personName' => 'Staff Within 7 Days',
                        ],
                    ],
                    // Event older than 7 days (e.g. 2025 backlog) - MUST BE DROPPED
                    [
                        'eventTime' => '2025-02-27T08:30:00+06:00',
                        'data'      => [
                            'personId'   => '106',
                            'personName' => 'Old Backlog Staff',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/hikcentral/event', $payload);
        $response->assertStatus(200);

        // User's active status today must NOT be touched by past events
        $user->refresh();
        $this->assertEquals('unassigned', $user->current_shift);

        // Event within 7 days MUST be stored
        $this->assertDatabaseHas('attendance_logs', [
            'employee_no' => '106',
            'user_id'     => $user->id,
            'person_name' => $user->name,
        ]);

        // Event older than 7 days MUST NOT be stored
        $this->assertDatabaseMissing('attendance_logs', [
            'person_name' => 'Old Backlog Staff',
        ]);
    }
}

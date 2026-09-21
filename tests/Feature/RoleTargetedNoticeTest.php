<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTargetedNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_role_targeted_notices()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/settings/notice', [
            'notice_active'          => '1',
            'notice_text'            => 'Global Maintenance Tonight',
            'notice_speed'           => '8',
            'notice_theme'           => 'danger',
            'notice_badge'           => 'GLOBAL ALERT',

            'notice_reseller_active' => '1',
            'notice_reseller_text'   => 'Reseller Bandwidth Update',
            'notice_reseller_speed'  => '6',
            'notice_reseller_theme'  => 'warning',
            'notice_reseller_badge'  => 'RESELLER INFO',

            'notice_noc_active'      => '1',
            'notice_noc_text'        => 'NOC Switch Config Maintenance',
            'notice_noc_speed'       => '10',
            'notice_noc_theme'       => 'indigo',
            'notice_noc_badge'       => 'NOC DISPATCH',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Global Maintenance Tonight', Setting::get('header_notice_text'));
        $this->assertEquals('Reseller Bandwidth Update', Setting::get('header_notice_reseller_text'));
        $this->assertEquals('NOC Switch Config Maintenance', Setting::get('header_notice_noc_text'));
    }

    public function test_reseller_sees_reseller_notice_on_page()
    {
        Setting::set('header_notice_text', 'Global Announcement');
        Setting::set('header_notice_active', '1');

        Setting::set('header_notice_reseller_text', 'Special Reseller Offer');
        Setting::set('header_notice_reseller_active', '1');
        Setting::set('header_notice_reseller_badge', 'RESELLER VIP');

        $reseller = User::factory()->create(['role' => 'reseller']);

        $response = $this->actingAs($reseller)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Special Reseller Offer');
        $response->assertSee('RESELLER VIP');
        $response->assertDontSee('Global Announcement');
    }

    public function test_noc_sees_noc_notice_on_page()
    {
        Setting::set('header_notice_text', 'Global Announcement');
        Setting::set('header_notice_active', '1');

        Setting::set('header_notice_noc_text', 'Core Fiber Cut Alert');
        Setting::set('header_notice_noc_active', '1');
        Setting::set('header_notice_noc_badge', 'NOC DISPATCH');

        $noc = User::factory()->create(['role' => 'noc']);

        $response = $this->actingAs($noc)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Core Fiber Cut Alert');
        $response->assertSee('NOC DISPATCH');
        $response->assertDontSee('Global Announcement');
    }

    public function test_reseller_sees_no_notice_when_reseller_notice_inactive()
    {
        Setting::set('header_notice_text', 'Global Announcement For Everyone');
        Setting::set('header_notice_active', '1');

        Setting::set('header_notice_reseller_text', 'Disabled Reseller Text');
        Setting::set('header_notice_reseller_active', '0');

        $reseller = User::factory()->create(['role' => 'reseller']);

        $response = $this->actingAs($reseller)->get('/profile');
        $response->assertStatus(200);
        $response->assertDontSee('Global Announcement For Everyone');
        $response->assertDontSee('Disabled Reseller Text');
    }

    public function test_master_switch_disables_all_notices()
    {
        Setting::set('header_notice_master_active', '0');

        Setting::set('header_notice_text', 'Global Notice');
        Setting::set('header_notice_active', '1');

        Setting::set('header_notice_reseller_text', 'Reseller Notice');
        Setting::set('header_notice_reseller_active', '1');

        Setting::set('header_notice_noc_text', 'NOC Notice');
        Setting::set('header_notice_noc_active', '1');

        $admin = User::factory()->create(['role' => 'admin']);
        $reseller = User::factory()->create(['role' => 'reseller']);
        $noc = User::factory()->create(['role' => 'noc']);

        $this->actingAs($admin)->get('/profile')->assertDontSee('Global Notice');
        $this->actingAs($reseller)->get('/profile')->assertDontSee('Reseller Notice');
        $this->actingAs($noc)->get('/profile')->assertDontSee('NOC Notice');
    }

    public function test_bilingual_notice_switches_between_english_and_bangla()
    {
        Setting::set('header_notice_reseller_active', '1');
        Setting::set('header_notice_reseller_text_en', 'English Notice For Reseller');
        Setting::set('header_notice_reseller_text_bn', 'রিসেলারদের জন্য বাংলা নোটিশ');
        Setting::set('header_notice_reseller_badge_en', 'RESELLER EN');
        Setting::set('header_notice_reseller_badge_bn', 'রিসেলার বি এন');

        $reseller = User::factory()->create(['role' => 'reseller', 'locale' => 'en']);

        // 1. In English mode
        $responseEn = $this->actingAs($reseller)->withSession(['locale' => 'en'])->get('/profile');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('English Notice For Reseller');
        $responseEn->assertSee('RESELLER EN');
        $responseEn->assertDontSee('রিসেলারদের জন্য বাংলা নোটিশ');

        // 2. In Bangla mode
        $responseBn = $this->actingAs($reseller)->withSession(['locale' => 'bn'])->get('/profile');
        $responseBn->assertStatus(200);
        $responseBn->assertSee('রিসেলারদের জন্য বাংলা নোটিশ');
        $responseBn->assertSee('রিসেলার বি এন');
        $responseBn->assertDontSee('English Notice For Reseller');
    }

    public function test_settings_notice_tab_labels_localize()
    {
        $admin = User::factory()->create(['role' => 'admin', 'locale' => 'en']);

        // English view
        $responseEn = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/settings');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Global Notice (For Everyone)');
        $responseEn->assertSee('Reseller Notice (For Resellers Only)');
        $responseEn->assertSee('NOC Notice (For NOC Team Only)');

        // Bangla view
        $responseBn = $this->actingAs($admin)->withSession(['locale' => 'bn'])->get('/settings');
        $responseBn->assertStatus(200);
        $responseBn->assertSee('গ্লোবাল নোটিশ (সবার জন্য)');
        $responseBn->assertSee('রিসেলার নোটিশ (শুধু রিসেলারদের জন্য)');
        $responseBn->assertSee('এনওসি নোটিশ (শুধু NOC টিমের জন্য)');
    }

    public function test_admin_can_quick_toggle_notice_channel()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Toggle NOC off
        $res = $this->actingAs($admin)->postJson('/settings/notice/toggle', [
            'channel' => 'noc',
            'active'  => false,
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true, 'channel' => 'noc', 'active' => false]);
        $this->assertEquals('0', Setting::get('header_notice_noc_active'));

        // Toggle NOC on
        $res2 = $this->actingAs($admin)->postJson('/settings/notice/toggle', [
            'channel' => 'noc',
            'active'  => true,
        ]);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'channel' => 'noc', 'active' => true]);
        $this->assertEquals('1', Setting::get('header_notice_noc_active'));
    }

    public function test_form_update_saves_inactive_channels_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/settings/notice', [
            'notice_master_active'   => '1',
            'notice_active'          => '0',
            'notice_reseller_active' => '0',
            'notice_noc_active'      => '0',
            'notice_text_en'         => 'Test',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1', Setting::get('header_notice_master_active'));
        $this->assertEquals('0', Setting::get('header_notice_active'));
        $this->assertEquals('0', Setting::get('header_notice_reseller_active'));
        $this->assertEquals('0', Setting::get('header_notice_noc_active'));
    }
}


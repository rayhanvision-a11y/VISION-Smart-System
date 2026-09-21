<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit(\App\Services\DatabaseBackupService $backupService)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('settings.edit', [
            'logoPath'     => Setting::get('logo_path'),
            'faviconPath'  => Setting::get('favicon_path'),
            // Role-based notices (Bilingual EN/BN)
            'noticeMasterActive'     => Setting::get('header_notice_master_active', '1'),
            'noticeTextEn'           => Setting::get('header_notice_text_en', Setting::get('header_notice_text', 'Alert — All portal services are fully operational.')),
            'noticeTextBn'           => Setting::get('header_notice_text_bn', Setting::get('header_notice_text', 'দৃষ্টি আকর্ষণ: আমাদের পোর্টালে সকল সেবা সচল রয়েছে।')),
            'noticeActive'           => Setting::get('header_notice_active', '0'),
            'noticeSpeed'            => Setting::get('header_notice_speed', '8'),
            'noticeTheme'            => Setting::get('header_notice_theme', 'danger'),
            'noticeBadgeEn'          => Setting::get('header_notice_badge_en', Setting::get('header_notice_badge', 'GLOBAL NOTICE')),
            'noticeBadgeBn'          => Setting::get('header_notice_badge_bn', 'সাধারণ নোটিশ'),

            'noticeResellerTextEn'   => Setting::get('header_notice_reseller_text_en', Setting::get('header_notice_reseller_text', '')),
            'noticeResellerTextBn'   => Setting::get('header_notice_reseller_text_bn', Setting::get('header_notice_reseller_text', '')),
            'noticeResellerActive'   => Setting::get('header_notice_reseller_active', '0'),
            'noticeResellerSpeed'    => Setting::get('header_notice_reseller_speed', '8'),
            'noticeResellerTheme'    => Setting::get('header_notice_reseller_theme', 'warning'),
            'noticeResellerBadgeEn'  => Setting::get('header_notice_reseller_badge_en', Setting::get('header_notice_reseller_badge', 'RESELLER ALERT')),
            'noticeResellerBadgeBn'  => Setting::get('header_notice_reseller_badge_bn', 'রিসেলার নোটিশ'),

            'noticeNocTextEn'        => Setting::get('header_notice_noc_text_en', Setting::get('header_notice_noc_text', '')),
            'noticeNocTextBn'        => Setting::get('header_notice_noc_text_bn', Setting::get('header_notice_noc_text', '')),
            'noticeNocActive'        => Setting::get('header_notice_noc_active', '0'),
            'noticeNocSpeed'         => Setting::get('header_notice_noc_speed', '8'),
            'noticeNocTheme'         => Setting::get('header_notice_noc_theme', 'indigo'),
            'noticeNocBadgeEn'       => Setting::get('header_notice_noc_badge_en', Setting::get('header_notice_noc_badge', 'NOC DISPATCH')),
            'noticeNocBadgeBn'       => Setting::get('header_notice_noc_badge_bn', 'এনওসি নোটিশ'),

            'themePrimary'   => Setting::get('theme_primary_color',   '#4f46e5'),
            'themeSecondary' => Setting::get('theme_secondary_color',  '#10b981'),
            'categories'     => \App\Models\TicketCategory::orderBy('name')->get(),
            'backups'        => $backupService->listBackups(),
        ]);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isSuperAdminOnly()) {
            abort(403);
        }

        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ]);

        $oldPath = Setting::get('logo_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('logo')->store('branding', 'public');
        Setting::set('logo_path', $path);

        return back()->with('status', 'Logo updated successfully.');
    }

    public function destroy()
    {
        if (!auth()->user()->isSuperAdminOnly()) {
            abort(403);
        }

        $oldPath = Setting::get('logo_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        Setting::set('logo_path', null);

        return back()->with('status', 'Logo removed.');
    }

    public function updateFavicon(Request $request)
    {
        if (!auth()->user()->isSuperAdminOnly()) {
            abort(403);
        }

        $request->validate([
            'favicon' => 'required|image|mimes:png,jpg,jpeg,ico,svg|max:512',
        ]);

        $oldPath = Setting::get('favicon_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('favicon')->store('branding', 'public');
        Setting::set('favicon_path', $path);

        return back()->with('status', 'Favicon updated successfully.');
    }

    public function destroyFavicon()
    {
        if (!auth()->user()->isSuperAdminOnly()) {
            abort(403);
        }

        $oldPath = Setting::get('favicon_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        Setting::set('favicon_path', null);

        return back()->with('status', 'Favicon removed.');
    }

    public function updateNotice(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            // Global Notice
            'notice_text_en'         => 'nullable|string|max:3000',
            'notice_text_bn'         => 'nullable|string|max:3000',
            'notice_text'            => 'nullable|string|max:3000',
            'notice_speed'           => 'nullable|integer|min:1|max:30',
            'notice_theme'           => 'nullable|string|in:danger,warning,info,success,indigo',
            'notice_badge_en'        => 'nullable|string|max:40',
            'notice_badge_bn'        => 'nullable|string|max:40',
            'notice_badge'           => 'nullable|string|max:40',

            // Reseller Dedicated Notice
            'notice_reseller_text_en'  => 'nullable|string|max:3000',
            'notice_reseller_text_bn'  => 'nullable|string|max:3000',
            'notice_reseller_text'     => 'nullable|string|max:3000',
            'notice_reseller_speed'    => 'nullable|integer|min:1|max:30',
            'notice_reseller_theme'    => 'nullable|string|in:danger,warning,info,success,indigo',
            'notice_reseller_badge_en' => 'nullable|string|max:40',
            'notice_reseller_badge_bn' => 'nullable|string|max:40',
            'notice_reseller_badge'    => 'nullable|string|max:40',

            // NOC Dedicated Notice
            'notice_noc_text_en'     => 'nullable|string|max:3000',
            'notice_noc_text_bn'     => 'nullable|string|max:3000',
            'notice_noc_text'        => 'nullable|string|max:3000',
            'notice_noc_speed'       => 'nullable|integer|min:1|max:30',
            'notice_noc_theme'       => 'nullable|string|in:danger,warning,info,success,indigo',
            'notice_noc_badge_en'    => 'nullable|string|max:40',
            'notice_noc_badge_bn'    => 'nullable|string|max:40',
            'notice_noc_badge'       => 'nullable|string|max:40',
        ]);

        // Master & Global Notice
        Setting::set('header_notice_master_active', $request->boolean('notice_master_active') ? '1' : '0');
        $globalTextEn = $validated['notice_text_en'] ?? $validated['notice_text'] ?? '';
        $globalTextBn = $validated['notice_text_bn'] ?? $validated['notice_text'] ?? '';
        Setting::set('header_notice_text_en', $globalTextEn);
        Setting::set('header_notice_text_bn', $globalTextBn);
        Setting::set('header_notice_text', !empty($globalTextBn) ? $globalTextBn : $globalTextEn);
        Setting::set('header_notice_active', $request->boolean('notice_active') ? '1' : '0');
        Setting::set('header_notice_speed', (string) ($validated['notice_speed'] ?? 8));
        Setting::set('header_notice_theme', $validated['notice_theme'] ?? 'danger');
        Setting::set('header_notice_badge_en', $validated['notice_badge_en'] ?? $validated['notice_badge'] ?? 'GLOBAL NOTICE');
        Setting::set('header_notice_badge_bn', $validated['notice_badge_bn'] ?? 'সাধারণ নোটিশ');
        Setting::set('header_notice_badge', $validated['notice_badge_en'] ?? $validated['notice_badge'] ?? 'GLOBAL NOTICE');

        // Reseller Dedicated Notice
        $resellerTextEn = $validated['notice_reseller_text_en'] ?? $validated['notice_reseller_text'] ?? '';
        $resellerTextBn = $validated['notice_reseller_text_bn'] ?? $validated['notice_reseller_text'] ?? '';
        Setting::set('header_notice_reseller_text_en', $resellerTextEn);
        Setting::set('header_notice_reseller_text_bn', $resellerTextBn);
        Setting::set('header_notice_reseller_text', !empty($resellerTextBn) ? $resellerTextBn : $resellerTextEn);
        Setting::set('header_notice_reseller_active', $request->boolean('notice_reseller_active') ? '1' : '0');
        Setting::set('header_notice_reseller_speed', (string) ($validated['notice_reseller_speed'] ?? 8));
        Setting::set('header_notice_reseller_theme', $validated['notice_reseller_theme'] ?? 'warning');
        Setting::set('header_notice_reseller_badge_en', $validated['notice_reseller_badge_en'] ?? $validated['notice_reseller_badge'] ?? 'RESELLER ALERT');
        Setting::set('header_notice_reseller_badge_bn', $validated['notice_reseller_badge_bn'] ?? 'রিসেলার নোটিশ');
        Setting::set('header_notice_reseller_badge', $validated['notice_reseller_badge_en'] ?? $validated['notice_reseller_badge'] ?? 'RESELLER ALERT');

        // NOC Dedicated Notice
        $nocTextEn = $validated['notice_noc_text_en'] ?? $validated['notice_noc_text'] ?? '';
        $nocTextBn = $validated['notice_noc_text_bn'] ?? $validated['notice_noc_text'] ?? '';
        Setting::set('header_notice_noc_text_en', $nocTextEn);
        Setting::set('header_notice_noc_text_bn', $nocTextBn);
        Setting::set('header_notice_noc_text', !empty($nocTextBn) ? $nocTextBn : $nocTextEn);
        Setting::set('header_notice_noc_active', $request->boolean('notice_noc_active') ? '1' : '0');
        Setting::set('header_notice_noc_speed', (string) ($validated['notice_noc_speed'] ?? 8));
        Setting::set('header_notice_noc_theme', $validated['notice_noc_theme'] ?? 'indigo');
        Setting::set('header_notice_noc_badge_en', $validated['notice_noc_badge_en'] ?? $validated['notice_noc_badge'] ?? 'NOC DISPATCH');
        Setting::set('header_notice_noc_badge_bn', $validated['notice_noc_badge_bn'] ?? 'এনওসি নোটিশ');
        Setting::set('header_notice_noc_badge', $validated['notice_noc_badge_en'] ?? $validated['notice_noc_badge'] ?? 'NOC DISPATCH');

        ActivityLog::record('setting_changed', 'Targeted bilingual role header notices updated');
        return back()->with('status', __('Role-targeted notices updated successfully!'));
    }

    public function toggleNotice(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'channel' => 'required|string|in:master,global,reseller,noc',
            'active'  => 'required|boolean',
        ]);

        $map = [
            'master'   => 'header_notice_master_active',
            'global'   => 'header_notice_active',
            'reseller' => 'header_notice_reseller_active',
            'noc'      => 'header_notice_noc_active',
        ];

        $key = $map[$validated['channel']];
        $val = $validated['active'] ? '1' : '0';
        Setting::set($key, $val);

        ActivityLog::record('setting_changed', "Notice channel [{$validated['channel']}] switched to " . ($val === '1' ? 'ON' : 'OFF'));

        $labelMap = [
            'master'   => __('Master Notice Switch'),
            'global'   => __('Global Notice'),
            'reseller' => __('Reseller Notice'),
            'noc'      => __('NOC Notice'),
        ];

        $stateText = $val === '1' ? __('turned ON') : __('turned OFF');
        $msg = "{$labelMap[$validated['channel']]} {$stateText}";

        return response()->json([
            'success' => true,
            'channel' => $validated['channel'],
            'active'  => (bool) $validated['active'],
            'message' => $msg,
        ]);
    }

    public function updateTheme(Request $request)
    {
        if (!auth()->user()->isSuperAdminOnly()) {
            abort(403);
        }

        $validated = $request->validate([
            'primary_color'   => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        Setting::set('theme_primary_color',   $validated['primary_color']);
        Setting::set('theme_secondary_color',  $validated['secondary_color']);

        ActivityLog::record('setting_changed', "Theme colors updated — primary: {$validated['primary_color']}, secondary: {$validated['secondary_color']}");
        return back()->with('status', __('Theme colors updated successfully!'));
    }

    public function backupExport()
    {
        if (!auth()->user()->isSuperAdminOnly()) abort(403);

        $settings = \Illuminate\Support\Facades\DB::table('settings')->get(['key', 'value']);
        $data = [
            'exported_at' => now()->toIso8601String(),
            'app_name'    => config('app.name'),
            'settings'    => $settings->pluck('value', 'key')->toArray(),
        ];

        ActivityLog::record('setting_changed', 'Settings backup exported');

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="settings-backup-' . now()->format('Y-m-d') . '.json"')
            ->header('Content-Type', 'application/json');
    }

    public function backupRestore(\Illuminate\Http\Request $request)
    {
        if (!auth()->user()->isSuperAdminOnly()) abort(403);

        $request->validate(['backup_file' => 'required|file|mimes:json|max:512']);

        $content = file_get_contents($request->file('backup_file')->getRealPath());
        $data    = json_decode($content, true);

        if (!isset($data['settings']) || !is_array($data['settings'])) {
            return back()->withErrors(['backup_file' => 'Invalid backup file format.']);
        }

        $allowed = [
            'logo_path', 'favicon_path', 'header_notice_text', 'header_notice_active',
            'header_notice_speed', 'theme_primary_color', 'theme_secondary_color',
        ];

        foreach ($data['settings'] as $key => $value) {
            if (in_array($key, $allowed)) {
                Setting::set($key, $value);
            }
        }

        ActivityLog::record('setting_changed', 'Settings restored from backup file');

        return back()->with('status', 'Settings restored successfully from backup.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('settings.edit', [
            'logoPath'     => Setting::get('logo_path'),
            'faviconPath'  => Setting::get('favicon_path'),
            'noticeText'   => Setting::get('header_notice_text', 'Alert — সম্মানিত POP ম্যানেজার ও রিসেলারদের দৃষ্টি আকর্ষণ করা যাচ্ছে: আমাদের পোর্টালে সকল সেবা সচল রয়েছে।'),
            'noticeActive' => Setting::get('header_notice_active', '1'),
            'noticeSpeed'    => Setting::get('header_notice_speed', '8'),
            'themePrimary'   => Setting::get('theme_primary_color',   '#4f46e5'),
            'themeSecondary' => Setting::get('theme_secondary_color',  '#10b981'),
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
            'notice_text'  => 'nullable|string|max:2000',
            'notice_speed' => 'nullable|integer|min:1|max:30',
        ]);

        Setting::set('header_notice_text', $validated['notice_text'] ?? '');
        Setting::set('header_notice_active', $request->has('notice_active') ? '1' : '0');
        Setting::set('header_notice_speed', (string) ($validated['notice_speed'] ?? 8));

        ActivityLog::record('setting_changed', 'Header notice updated');
        return back()->with('status', __('Header notice marquee updated successfully!'));
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

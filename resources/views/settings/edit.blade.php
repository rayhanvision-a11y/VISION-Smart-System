<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-lg font-bold text-slate-800 mb-1">{{ __('Branding Settings') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ __('Upload your company logo. It will appear in the sidebar and login page.') }}</p>

        @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
            {{ session('status') }}
        </div>
        @endif

        {{-- Header Notice Ticker Card --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide mb-1 flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-red-500 text-white">📢 NOTICE</span>
                {{ __('Header Announcement Marquee Ticker') }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                {{ __('This notice scrolls from right to left in the top header. Both Admin and Super Admin can edit the text and toggle visibility.') }}
            </p>

            <form method="POST" action="{{ route('settings.notice.update') }}" class="space-y-4">
                @csrf

                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block">{{ __('Enable Notice Ticker') }}</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ __('Show scrolling announcement bar in top header.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notice_active" value="1" {{ ($noticeActive ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Notice Text (Multiple Line Support)') }} *</label>
                    <textarea name="notice_text" rows="4" required placeholder="নোটিশ ১: সম্মানিত POP ম্যানেজারদের দৃষ্টি আকর্ষণ করা যাচ্ছে...&#10;নোটিশ ২: পোর্টাল থেকে সরাসরি বিলিং সার্ভিস পরিচালনা করতে পারবেন।&#10;নোটিশ ৩: নতুন টিকিটের আপডেটের জন্য ড্যাশবোর্ড চেক করুন।"
                              class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 leading-relaxed">{{ old('notice_text', $noticeText ?? '') }}</textarea>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                        💡 <strong>একাধিক নোটিশ দেওয়া নিয়ম:</strong> প্রতিটি নোটিশ নতুন লাইনে (Enter চেপে) অথবা <code class="bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded text-red-600 font-bold">|</code> (পাইপ) সিম্বল দিয়ে আলাদা করে লিখুন। একাধিক নোটিশ পরপর লাল ডট (•) দিয়ে হেডারে স্ক্রোল করবে।
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-1.5">{{ __('Scroll Speed') }}</label>
                        <select name="notice_speed" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            <option value="4" {{ ($noticeSpeed ?? 8) == 4 ? 'selected' : '' }}>Slow (4)</option>
                            <option value="8" {{ ($noticeSpeed ?? 8) == 8 ? 'selected' : '' }}>Normal (8 - Recommended)</option>
                            <option value="12" {{ ($noticeSpeed ?? 8) == 12 ? 'selected' : '' }}>Fast (12)</option>
                            <option value="16" {{ ($noticeSpeed ?? 8) == 16 ? 'selected' : '' }}>Very Fast (16)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md">
                        {{ __('Save Notice Settings') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Theme Color Card (Super Admin Only) --}}
        @if(auth()->user()->isSuperAdminOnly())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide mb-1 flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-indigo-600 text-white">🎨 THEME</span>
                {{ __('Site Theme Colors') }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                {{ __('Set the primary and secondary brand colors for the entire site. Changes apply instantly on next page load.') }}
            </p>

            <form method="POST" action="{{ route('settings.theme.update') }}" id="form-theme">
                @csrf

                {{-- Preset Swatches --}}
                <div class="mb-5">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide mb-3">{{ __('Quick Presets') }}</p>
                    <div class="flex flex-wrap gap-2" id="theme-presets">
                        @php
                        $presets = [
                            ['label' => 'Indigo (Default)', 'primary' => '#4f46e5', 'secondary' => '#10b981'],
                            ['label' => 'Violet',           'primary' => '#7c3aed', 'secondary' => '#06b6d4'],
                            ['label' => 'Blue',             'primary' => '#2563eb', 'secondary' => '#10b981'],
                            ['label' => 'Sky',              'primary' => '#0284c7', 'secondary' => '#f59e0b'],
                            ['label' => 'Teal',             'primary' => '#0d9488', 'secondary' => '#8b5cf6'],
                            ['label' => 'Rose',             'primary' => '#e11d48', 'secondary' => '#f59e0b'],
                            ['label' => 'Orange',           'primary' => '#ea580c', 'secondary' => '#3b82f6'],
                            ['label' => 'Slate',            'primary' => '#475569', 'secondary' => '#10b981'],
                        ];
                        @endphp
                        @foreach($presets as $preset)
                        <button type="button"
                            class="theme-preset-btn flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:border-slate-400 dark:hover:border-slate-500 transition-all text-xs font-semibold text-slate-700 dark:text-slate-200"
                            data-primary="{{ $preset['primary'] }}" data-secondary="{{ $preset['secondary'] }}"
                            title="{{ $preset['label'] }}">
                            <span style="display:inline-flex;gap:3px;">
                                <span style="width:14px;height:14px;border-radius:50%;background:{{ $preset['primary'] }};display:inline-block;"></span>
                                <span style="width:14px;height:14px;border-radius:50%;background:{{ $preset['secondary'] }};display:inline-block;"></span>
                            </span>
                            {{ $preset['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Color Pickers --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2">
                            {{ __('Primary Color') }}
                            <span class="text-[10px] font-normal text-slate-400 ml-1">{{ __('Sidebar, buttons, active links') }}</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="primary-color-picker" value="{{ $themePrimary }}"
                                class="w-12 h-10 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-0.5"
                                oninput="document.getElementById('primary-color-hex').value=this.value; updateThemePreview()">
                            <input type="text" id="primary-color-hex" name="primary_color" value="{{ $themePrimary }}" maxlength="7"
                                class="flex-1 px-3 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 uppercase"
                                oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('primary-color-picker').value=this.value;updateThemePreview()}"
                                placeholder="#4f46e5">
                        </div>
                        <div id="primary-preview-bar" style="height:6px;border-radius:4px;margin-top:8px;background:{{ $themePrimary }};transition:background 0.3s;"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-2">
                            {{ __('Secondary Color') }}
                            <span class="text-[10px] font-normal text-slate-400 ml-1">{{ __('Success badges, toggles') }}</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="secondary-color-picker" value="{{ $themeSecondary }}"
                                class="w-12 h-10 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer bg-transparent p-0.5"
                                oninput="document.getElementById('secondary-color-hex').value=this.value; updateThemePreview()">
                            <input type="text" id="secondary-color-hex" name="secondary_color" value="{{ $themeSecondary }}" maxlength="7"
                                class="flex-1 px-3 py-2 text-sm font-mono border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 uppercase"
                                oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('secondary-color-picker').value=this.value;updateThemePreview()}"
                                placeholder="#10b981">
                        </div>
                        <div id="secondary-preview-bar" style="height:6px;border-radius:4px;margin-top:8px;background:{{ $themeSecondary }};transition:background 0.3s;"></div>
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-5">
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Live Preview</span>
                    </div>
                    <div class="p-4 bg-white dark:bg-slate-900 flex flex-wrap gap-3 items-center">
                        <button type="button" id="prev-btn-primary" style="padding:7px 16px;border-radius:9px;font-size:12px;font-weight:700;color:white;border:none;background:{{ $themePrimary }};">Primary Button</button>
                        <button type="button" id="prev-btn-secondary" style="padding:7px 16px;border-radius:9px;font-size:12px;font-weight:700;color:white;border:none;background:{{ $themeSecondary }};">Secondary Button</button>
                        <span id="prev-badge-primary" style="padding:3px 12px;border-radius:999px;font-size:11px;font-weight:700;color:white;background:{{ $themePrimary }};">Badge</span>
                        <span id="prev-badge-secondary" style="padding:3px 12px;border-radius:999px;font-size:11px;font-weight:700;color:white;background:{{ $themeSecondary }};">Status</span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:12px;font-weight:600;color:#64748b;">Active Link:</span>
                            <span id="prev-link" style="font-size:12px;font-weight:700;color:{{ $themePrimary }};">Dashboard →</span>
                        </div>
                        <div id="prev-bar" style="width:100%;height:4px;border-radius:4px;background:linear-gradient(90deg,{{ $themePrimary }},{{ $themeSecondary }});margin-top:4px;"></div>
                    </div>
                </div>

                <div class="pt-1">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md">
                        {{ __('Save Theme Colors') }}
                    </button>
                    <span class="text-xs text-slate-400 ml-3">{{ __('Applies site-wide on next page load.') }}</span>
                </div>
            </form>
        </div>

        <script>
        function updateThemePreview() {
            const p = document.getElementById('primary-color-hex').value;
            const s = document.getElementById('secondary-color-hex').value;
            const validHex = /^#[0-9a-fA-F]{6}$/;
            if (validHex.test(p)) {
                document.getElementById('primary-preview-bar').style.background = p;
                document.getElementById('prev-btn-primary').style.background = p;
                document.getElementById('prev-badge-primary').style.background = p;
                document.getElementById('prev-link').style.color = p;
            }
            if (validHex.test(s)) {
                document.getElementById('secondary-preview-bar').style.background = s;
                document.getElementById('prev-btn-secondary').style.background = s;
                document.getElementById('prev-badge-secondary').style.background = s;
            }
            if (validHex.test(p) && validHex.test(s)) {
                document.getElementById('prev-bar').style.background = `linear-gradient(90deg,${p},${s})`;
            }
        }
        document.querySelectorAll('.theme-preset-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const p = this.dataset.primary;
                const s = this.dataset.secondary;
                document.getElementById('primary-color-picker').value = p;
                document.getElementById('primary-color-hex').value = p;
                document.getElementById('secondary-color-picker').value = s;
                document.getElementById('secondary-color-hex').value = s;
                updateThemePreview();
                document.querySelectorAll('.theme-preset-btn').forEach(b => b.classList.remove('ring-2','ring-indigo-500'));
                this.classList.add('ring-2','ring-indigo-500');
            });
        });
        </script>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-2">{{ __('Current Logo') }}</p>
            <div class="mb-5 flex items-center justify-center bg-slate-50 border border-slate-200 rounded-lg p-6">
                @if($logoPath)
                <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="max-h-24 w-auto object-contain">
                @else
                <span class="text-sm text-slate-400">{{ __('No logo uploaded yet.') }}</span>
                @endif
            </div>

            <form method="POST" action="{{ route('settings.logo.update') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-xs text-slate-400 font-semibold uppercase tracking-wide mb-2">{{ __('Upload New Logo') }}</label>
                <input type="file" name="logo" accept="image/*" required
                       class="block w-full text-sm text-slate-600 border border-slate-200 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-400 mt-2">{{ __('PNG, JPG, WEBP or SVG. Max 2MB.') }}</p>

                <div class="flex items-center gap-3 mt-5">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        {{ __('Upload') }}
                    </button>
                    @if($logoPath)
                    <button type="submit" form="remove-logo-form" class="text-sm text-red-500 hover:text-red-700 px-3 py-2.5">
                        {{ __('Remove Logo') }}
                    </button>
                    @endif
                </div>
            </form>

            @if($logoPath)
            <form id="remove-logo-form" method="POST" action="{{ route('settings.logo.destroy') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>

        {{-- Favicon --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mt-6">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-2">{{ __('Current Favicon') }}</p>
            <p class="text-xs text-slate-400 mb-4">{{ __('Shown in the browser tab, bookmarks, and mobile home screen icons.') }}</p>
            <div class="mb-5 flex items-center justify-center bg-slate-50 border border-slate-200 rounded-lg p-6">
                @if($faviconPath)
                <img src="{{ asset('storage/' . $faviconPath) }}" alt="Favicon" class="max-h-16 w-auto object-contain">
                @else
                <span class="text-sm text-slate-400">{{ __('No favicon uploaded yet.') }}</span>
                @endif
            </div>

            <form method="POST" action="{{ route('settings.favicon.update') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-xs text-slate-400 font-semibold uppercase tracking-wide mb-2">{{ __('Upload New Favicon') }}</label>
                <input type="file" name="favicon" accept="image/*,.ico" required
                       class="block w-full text-sm text-slate-600 border border-slate-200 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                @error('favicon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-400 mt-2">{{ __('PNG, JPG, ICO or SVG. Square image recommended (e.g. 512×512). Max 512KB.') }}</p>

                <div class="flex items-center gap-3 mt-5">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        {{ __('Upload') }}
                    </button>
                    @if($faviconPath)
                    <button type="submit" form="remove-favicon-form" class="text-sm text-red-500 hover:text-red-700 px-3 py-2.5">
                        {{ __('Remove Favicon') }}
                    </button>
                    @endif
                </div>
            </form>

            @if($faviconPath)
            <form id="remove-favicon-form" method="POST" action="{{ route('settings.favicon.destroy') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>

        {{-- Backup & Restore (Super Admin Only) --}}
        @if(auth()->user()->isSuperAdminOnly())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 mt-6">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide mb-1 flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-amber-500 text-white">💾 BACKUP</span>
                {{ __('Backup & Restore Settings') }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                {{ __('Export all site settings to a JSON file, or restore from a previous backup. Only settings are backed up — database records are not included.') }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Export --}}
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">📤</span>
                        <span class="font-bold text-sm text-slate-700 dark:text-slate-200">Export Backup</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Download all current settings as a JSON file.</p>
                    <a href="{{ route('settings.backup.export') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Backup
                    </a>
                </div>

                {{-- Restore --}}
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg">📥</span>
                        <span class="font-bold text-sm text-slate-700 dark:text-slate-200">Restore Backup</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Upload a previously exported JSON backup file.</p>
                    <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data">
                        @csrf
                        @error('backup_file')
                            <p class="text-red-500 text-xs mb-2">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center gap-2">
                            <input type="file" name="backup_file" accept=".json" required
                                class="flex-1 text-xs text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer focus:outline-none file:mr-2 file:py-1.5 file:px-3 file:rounded-l-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                            <button type="submit"
                                onclick="return confirm('Restore settings from this file? This will overwrite current settings.')"
                                class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition-all whitespace-nowrap">
                                Restore
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

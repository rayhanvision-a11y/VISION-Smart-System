<x-app-layout>
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">{{ __('My Profile') }}</h1>

        {{-- Profile Photo --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">{{ __('Profile Photo') }}</h2>
            <div class="flex items-center gap-5">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                     class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 flex-shrink-0">
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Upload new photo') }}</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <button type="submit" class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">{{ __('Update Photo') }}</button>
                </form>
            </div>
        </div>

        {{-- Profile Info --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">{{ __('Profile Information') }}</h2>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Preferences --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">{{ __('Preferences') }}</h2>

            @if(session('status') === 'preferences-updated')
            <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                {{ __('Preferences updated successfully.') }}
            </div>
            @endif

            <form method="POST" action="{{ route('profile.preferences') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Theme') }}</label>
                        <select name="theme_preference" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="system" {{ auth()->user()->theme_preference === 'system' ? 'selected' : '' }}>{{ __('System default') }}</option>
                            <option value="light"  {{ auth()->user()->theme_preference === 'light'  ? 'selected' : '' }}>{{ __('Light') }}</option>
                            <option value="dark"   {{ auth()->user()->theme_preference === 'dark'   ? 'selected' : '' }}>{{ __('Dark') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Language') }}</label>
                        <select name="locale" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="en" {{ auth()->user()->locale === 'en' || !auth()->user()->locale ? 'selected' : '' }}>English</option>
                            <option value="bn" {{ auth()->user()->locale === 'bn' ? 'selected' : '' }}>বাংলা (Bangla)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Timezone') }}</label>
                        <select name="timezone" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('System default') }} ({{ config('app.timezone') }})</option>
                            @foreach(['Asia/Dhaka','Asia/Kolkata','Asia/Dubai','Asia/Singapore','Asia/Bangkok','Europe/London','America/New_York','UTC'] as $tz)
                            <option value="{{ $tz }}" {{ auth()->user()->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Email notifications') }}</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="hidden" name="notify_on_assign" value="0">
                            <input type="checkbox" name="notify_on_assign" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ auth()->user()->notify_on_assign ? 'checked' : '' }}>
                            {{ __('Ticket assigned or reassigned to me') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="hidden" name="notify_on_resolve" value="0">
                            <input type="checkbox" name="notify_on_resolve" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ auth()->user()->notify_on_resolve ? 'checked' : '' }}>
                            {{ __('My ticket has been resolved') }}
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="hidden" name="notify_on_message" value="0">
                            <input type="checkbox" name="notify_on_message" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" {{ auth()->user()->notify_on_message ? 'checked' : '' }}>
                            {{ __('New message on my tickets') }}
                        </label>
                    </div>
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">{{ __('Save Preferences') }}</button>
            </form>
        </div>

        {{-- Two-Factor Authentication --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-700 mb-1">{{ __('Two-Factor Authentication') }}</h2>
                    <p class="text-sm text-slate-500">
                        @if(auth()->user()->two_factor_enabled)
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>{{ __('Enabled') }}</span>
                        @else
                            {{ __('Add an extra layer of security to your account.') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('two-factor.show') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex-shrink-0">{{ __('Manage') }}</a>
            </div>
        </div>

        {{-- Password --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">{{ __('Update Password') }}</h2>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete Account --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>

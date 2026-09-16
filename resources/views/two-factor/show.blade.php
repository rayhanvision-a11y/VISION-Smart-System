<x-app-layout>
    <div class="max-w-xl">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">{{ __('Two-Factor Authentication') }}</h1>

        @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">{{ session('status') }}</div>
        @endif

        @if(session('2fa_show_recovery_codes'))
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-amber-800 mb-2">{{ __('Save your recovery codes') }}</h2>
            <p class="text-xs text-amber-700 mb-3">{{ __('Each code can be used once if you lose access to your authenticator app. Store them somewhere safe — they will not be shown again.') }}</p>
            <div class="grid grid-cols-2 gap-2 font-mono text-xs bg-white border border-amber-200 rounded-lg p-3">
                @foreach(session('2fa_show_recovery_codes') as $code)
                <span>{{ $code }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            @if($enabled)
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <p class="text-sm font-semibold text-slate-700">{{ __('Two-factor authentication is enabled') }}</p>
            </div>
            <p class="text-sm text-slate-500 mb-5">{{ __("You'll be asked for a 6-digit code from your authenticator app each time you log in.") }}</p>
            <form method="POST" action="{{ route('two-factor.disable') }}" class="max-w-xs">
                @csrf @method('DELETE')
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">{{ __('Confirm password to disable') }}</label>
                <input type="password" name="password" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50 mb-3">
                @error('password')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">{{ __('Disable 2FA') }}</button>
            </form>
            @else
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <p class="text-sm font-semibold text-slate-700">{{ __('Two-factor authentication is not enabled') }}</p>
            </div>
            <p class="text-sm text-slate-500 mb-5">{{ __('Add an extra layer of security to your account using an authenticator app (Google Authenticator, Authy, etc.).') }}</p>
            <a href="{{ route('two-factor.enroll') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">{{ __('Enable 2FA') }}</a>
            @endif
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="max-w-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">{{ __('Enable Two-Factor Authentication') }}</h1>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <ol class="space-y-5">
                <li>
                    <p class="text-sm font-semibold text-slate-700 mb-3">1. {{ __('Scan this QR code with your authenticator app') }}</p>
                    <div class="flex justify-center bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($otpauthUrl) }}" alt="QR Code" width="200" height="200">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">{{ __("Can't scan? Enter this code manually:") }} <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">{{ $secret }}</code></p>
                </li>
                <li>
                    <p class="text-sm font-semibold text-slate-700 mb-2">2. {{ __('Enter the 6-digit code shown in your app') }}</p>
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex items-center gap-3">
                        @csrf
                        <input type="text" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required
                               class="w-32 border border-slate-200 rounded-lg px-3 py-2 text-lg text-center tracking-widest font-mono bg-slate-50 focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">{{ __('Verify & Enable') }}</button>
                    </form>
                    @error('code')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                </li>
            </ol>
        </div>

        <a href="{{ route('two-factor.show') }}" class="inline-block mt-4 text-sm text-slate-500 hover:text-slate-700">← {{ __('Cancel') }}</a>
    </div>
</x-app-layout>

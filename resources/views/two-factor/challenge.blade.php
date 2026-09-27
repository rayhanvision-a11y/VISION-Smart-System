<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Two-Factor Authentication') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5">
            {{ __('Enter the 6-digit code from your authenticator app or one of your recovery codes.') }}
        </p>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="mb-4 p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs sm:text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="space-y-4">
        @csrf
        <div>
            <label for="code" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2 text-center">
                {{ __('Authentication Code') }}
            </label>
            <input 
                id="code" 
                class="login-input w-full px-4 py-3 rounded-xl text-center tracking-[0.4em] font-mono text-lg font-bold" 
                type="text" 
                name="code" 
                inputmode="numeric" 
                autocomplete="one-time-code" 
                placeholder="000000"
                autofocus 
                required 
            />
        </div>

        <div class="pt-2">
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Verify Identity') }}
            </button>
        </div>
    </form>

    <div class="text-center pt-3">
        <a href="{{ route('login') }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
            ← {{ __('Back to login') }}
        </a>
    </div>
</x-guest-layout>

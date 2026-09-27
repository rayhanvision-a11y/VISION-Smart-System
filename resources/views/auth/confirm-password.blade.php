<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Confirm Password') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5">
            {{ __('This is a secure area of the system. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Password') }}
            </label>
            <input 
                id="password" 
                class="login-input w-full px-4 py-3 rounded-xl text-sm shadow-sm" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password" 
                placeholder="••••••••••••"
                autofocus
            />
            <x-input-error :messages="$errors?->get('password')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <div class="pt-2">
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Confirm & Continue') }}
            </button>
        </div>
    </form>
</x-guest-layout>

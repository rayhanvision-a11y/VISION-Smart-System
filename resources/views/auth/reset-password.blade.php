<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Set New Password') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5">
            {{ __('Please create a strong new password for your account.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Email Address') }}
            </label>
            <input 
                id="email" 
                class="login-input w-full px-4 py-3 rounded-xl text-sm shadow-sm" 
                type="email" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus 
                autocomplete="username" 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('New Password') }}
            </label>
            <input 
                id="password" 
                class="login-input w-full px-4 py-3 rounded-xl text-sm shadow-sm" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password" 
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Confirm Password') }}
            </label>
            <input 
                id="password_confirmation" 
                class="login-input w-full px-4 py-3 rounded-xl text-sm shadow-sm" 
                type="password" 
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <div class="pt-2">
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Reset Password & Sign In') }}
            </button>
        </div>
    </form>
</x-guest-layout>

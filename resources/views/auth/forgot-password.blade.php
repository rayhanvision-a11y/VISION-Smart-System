<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Reset Password') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5 leading-relaxed">
            {{ __('Forgot your password? Enter your registered email address and we will send you a password reset link.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

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
                :value="old('email')" 
                placeholder="name@visiontech.com.bd"
                required 
                autofocus 
            />
            <x-input-error :messages="$errors?->get('email')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <div class="pt-2">
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                ← {{ __('Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>

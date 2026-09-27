<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Create Staff Account') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5">
            {{ __('Register a new account to access the service desk.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Full Name') }}
            </label>
            <input 
                id="name" 
                class="login-input w-full px-4 py-3 rounded-xl text-sm shadow-sm" 
                type="text" 
                name="name" 
                value="{{ old('name') }}" 
                required 
                autofocus 
                autocomplete="name" 
                placeholder="John Doe"
            />
            <x-input-error :messages="$errors?->get('name')" class="mt-2 text-rose-400 text-xs" />
        </div>

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
                value="{{ old('email') }}" 
                required 
                autocomplete="username" 
                placeholder="name@visiontech.com.bd"
            />
            <x-input-error :messages="$errors?->get('email')" class="mt-2 text-rose-400 text-xs" />
        </div>

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
                autocomplete="new-password" 
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors?->get('password')" class="mt-2 text-rose-400 text-xs" />
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
                placeholder="••••••••••••"
            />
            <x-input-error :messages="$errors?->get('password_confirmation')" class="mt-2 text-rose-400 text-xs" />
        </div>

        <div class="pt-2">
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Register Account') }}
            </button>
        </div>

        <div class="text-center pt-2">
            <a class="text-xs text-blue-400 hover:text-blue-300 transition-colors" href="{{ route('login') }}">
                {{ __('Already registered? Sign in') }}
            </a>
        </div>
    </form>
</x-guest-layout>

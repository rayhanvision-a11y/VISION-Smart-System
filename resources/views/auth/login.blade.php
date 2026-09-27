<x-guest-layout>
    <!-- Card Header / Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-white">
            Welcome Back
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5">
            Sign in to access tickets, staff dispatch & network operations
        </p>
    </div>

    <!-- Session Status Notification -->
    @if (session('status'))
        <div class="mb-5 p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Email Address') }}
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="name@visiontech.com.bd"
                    class="login-input w-full pl-10 pr-4 py-3 rounded-xl text-sm shadow-sm"
                />
            </div>
            @if (isset($errors) && $errors->has('email'))
                <p class="mt-2 text-xs text-rose-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </p>
            @endif
        </div>

        <!-- Password Field with Show/Hide Toggle -->
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input 
                    id="password" 
                    :type="show ? 'text' : 'password'" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••••••"
                    class="login-input w-full pl-10 pr-11 py-3 rounded-xl text-sm shadow-sm"
                />
                <button 
                    type="button" 
                    @click="show = !show" 
                    tabindex="-1"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition-colors focus:outline-none cursor-pointer"
                    :title="show ? 'Hide password' : 'Show password'">
                    <!-- Eye Off -->
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                    <!-- Eye On -->
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            @if (isset($errors) && $errors->has('password'))
                <p class="mt-2 text-xs text-rose-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first('password') }}</span>
                </p>
            @endif
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500/40 cursor-pointer"
                />
                <span class="text-xs sm:text-sm text-slate-300 font-medium">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="login-btn w-full relative group overflow-hidden rounded-xl text-white font-semibold py-3.5 px-5 flex items-center justify-center gap-2 cursor-pointer text-sm">
                <span>{{ __('Log in to Portal') }}</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

    </form>
</x-guest-layout>

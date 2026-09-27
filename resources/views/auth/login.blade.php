<x-guest-layout>
    <!-- Card Header / Title -->
    <div>
        <h1 class="auth-heading">
            Welcome Back
        </h1>
        <p class="auth-subheading">
            Sign in to access tickets, staff dispatch & network operations
        </p>
    </div>

    <!-- Session Status Notification -->
    @if (session('status'))
        <div style="margin-bottom: 20px; padding: 12px 14px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px; flex-shrink: 0; color: #10b981;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-field">
            <label for="email" class="field-label">
                {{ __('Email Address') }}
            </label>
            <div class="field-input-box">
                <span class="field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </span>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="name@visiontech.com.bd"
                    class="input-control"
                />
            </div>
            @if (isset($errors) && $errors->has('email'))
                <p class="field-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </p>
            @endif
        </div>

        <!-- Password Field with Show/Hide Toggle -->
        <div class="form-field" x-data="{ show: false }">
            <div class="field-label-row">
                <label for="password" class="field-label">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="field-link">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="field-input-box">
                <span class="field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <input 
                    id="password" 
                    :type="show ? 'text' : 'password'" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••••••"
                    class="input-control has-toggle"
                />
                <button 
                    type="button" 
                    @click="show = !show" 
                    tabindex="-1"
                    class="eye-toggle-btn"
                    :title="show ? 'Hide password' : 'Show password'">
                    <!-- Eye Off -->
                    <svg x-show="show" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <!-- Eye On -->
                    <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            @if (isset($errors) && $errors->has('password'))
                <p class="field-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first('password') }}</span>
                </p>
            @endif
        </div>

        <!-- Remember Me Checkbox -->
        <div class="remember-row">
            <label for="remember_me" class="checkbox-label">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="custom-checkbox"
                />
                <span>{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="btn-primary-action">
                <span>{{ __('Log In to Portal') }}</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </div>

    </form>
</x-guest-layout>

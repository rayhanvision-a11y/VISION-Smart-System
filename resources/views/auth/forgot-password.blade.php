<x-guest-layout>
    <div>
        <h1 class="auth-heading">
            {{ __('Reset Password') }}
        </h1>
        <p class="auth-subheading">
            {{ __('Forgot your password? Enter your registered email address and we will send you a password reset link.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status style="margin-bottom: 20px;" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
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
                    class="input-control" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="name@visiontech.com.bd"
                    required 
                    autofocus 
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

        <div style="margin-top: 24px;">
            <button type="submit" class="btn-primary-action">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('login') }}" class="field-link">
                ← {{ __('Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div>
        <h1 class="auth-heading">
            {{ __('Create Staff Account') }}
        </h1>
        <p class="auth-subheading">
            {{ __('Register a new account to access the service desk.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-field">
            <label for="name" class="field-label">
                {{ __('Full Name') }}
            </label>
            <div class="field-input-box">
                <span class="field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </span>
                <input 
                    id="name" 
                    class="input-control" 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    autofocus 
                    autocomplete="name" 
                    placeholder="John Doe"
                />
            </div>
            @if (isset($errors) && $errors->has('name'))
                <p class="field-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first('name') }}</span>
                </p>
            @endif
        </div>

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
                    required 
                    autocomplete="username" 
                    placeholder="name@visiontech.com.bd"
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

        <!-- Password -->
        <div class="form-field">
            <label for="password" class="field-label">
                {{ __('Password') }}
            </label>
            <div class="field-input-box">
                <span class="field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <input 
                    id="password" 
                    class="input-control"
                    type="password"
                    name="password"
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••••••"
                />
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

        <!-- Confirm Password -->
        <div class="form-field">
            <label for="password_confirmation" class="field-label">
                {{ __('Confirm Password') }}
            </label>
            <div class="field-input-box">
                <span class="field-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <input 
                    id="password_confirmation" 
                    class="input-control"
                    type="password"
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••••••"
                />
            </div>
            @if (isset($errors) && $errors->has('password_confirmation'))
                <p class="field-error">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first('password_confirmation') }}</span>
                </p>
            @endif
        </div>

        <div style="margin-top: 24px;">
            <button type="submit" class="btn-primary-action">
                {{ __('Register Account') }}
            </button>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a class="field-link" href="{{ route('login') }}">
                {{ __('Already registered? Sign in') }}
            </a>
        </div>
    </form>
</x-guest-layout>

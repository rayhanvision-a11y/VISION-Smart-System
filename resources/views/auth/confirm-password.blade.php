<x-guest-layout>
    <div>
        <h1 class="auth-heading">
            {{ __('Confirm Password') }}
        </h1>
        <p class="auth-subheading">
            {{ __('This is a secure area of the system. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

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
                    autocomplete="current-password" 
                    placeholder="••••••••••••"
                    autofocus
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

        <div style="margin-top: 24px;">
            <button type="submit" class="btn-primary-action">
                {{ __('Confirm & Continue') }}
            </button>
        </div>
    </form>
</x-guest-layout>

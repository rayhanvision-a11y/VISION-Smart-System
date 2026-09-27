<x-guest-layout>
    <div>
        <h1 class="auth-heading">
            {{ __('Two-Factor Authentication') }}
        </h1>
        <p class="auth-subheading">
            {{ __('Enter the 6-digit code from your authenticator app or one of your recovery codes.') }}
        </p>
    </div>

    @if(isset($errors) && $errors->any())
    <div style="margin-bottom: 20px; padding: 12px 14px; border-radius: 12px; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #fca5a5; font-size: 13px;">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
        @csrf
        <div class="form-field">
            <label for="code" class="field-label" style="text-align: center;">
                {{ __('Authentication Code') }}
            </label>
            <input 
                id="code" 
                class="input-control" 
                style="text-align: center; letter-spacing: 0.4em; font-family: monospace; font-size: 18px; font-weight: 700; padding: 0 16px !important;"
                type="text" 
                name="code" 
                inputmode="numeric" 
                autocomplete="one-time-code" 
                placeholder="000000"
                autofocus 
                required 
            />
        </div>

        <div style="margin-top: 24px;">
            <button type="submit" class="btn-primary-action">
                {{ __('Verify Identity') }}
            </button>
        </div>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('login') }}" class="field-link">
            ← {{ __('Back to login') }}
        </a>
    </div>
</x-guest-layout>

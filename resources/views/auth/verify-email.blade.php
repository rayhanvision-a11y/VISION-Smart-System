<x-guest-layout>
    <div>
        <h1 class="auth-heading">
            {{ __('Verify Your Email') }}
        </h1>
        <p class="auth-subheading">
            {{ __('Thanks for signing up! Please verify your email address by clicking on the link we just sent to your inbox.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div style="margin-bottom: 20px; padding: 12px 14px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; font-size: 13px;">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 14px;">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary-action">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="text-align: center; margin-top: 6px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #94a3b8; font-size: 12px; cursor: pointer; text-decoration: underline;">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

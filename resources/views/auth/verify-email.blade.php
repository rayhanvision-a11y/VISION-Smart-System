<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold tracking-tight text-white">
            {{ __('Verify Your Email') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 mt-1.5 leading-relaxed">
            {{ __('Thanks for signing up! Please verify your email address by clicking on the link we just sent to your inbox.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="mt-4 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="login-btn w-full rounded-xl text-white font-semibold py-3.5 px-5 text-sm cursor-pointer shadow-lg">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-xs text-slate-400 hover:text-white transition-colors cursor-pointer">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>

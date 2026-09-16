<x-guest-layout>
    <p class="text-sm text-gray-600 mb-4">{{ __('Enter the 6-digit code from your authenticator app, or one of your recovery codes.') }}</p>

    @if($errors->any())
    <div class="mb-4 text-sm text-red-600">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
        @csrf
        <x-input-label for="code" value="{{ __('Authentication Code') }}" />
        <x-text-input id="code" class="block mt-1 w-full text-center tracking-widest font-mono" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>{{ __('Verify') }}</x-primary-button>
        </div>
    </form>

    <a href="{{ route('login') }}" class="block mt-4 text-sm text-gray-500 hover:text-gray-700 underline">{{ __('Back to login') }}</a>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-ui.alert type="success" class="mb-4">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </x-ui.alert>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-ui.button type="submit" variant="primary">
                    {{ __('Resend Verification Email') }}
                </x-ui.button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-ui.button type="submit" variant="secondary">
                {{ __('Log Out') }}
            </x-ui.button>
        </form>
    </div>
</x-guest-layout>

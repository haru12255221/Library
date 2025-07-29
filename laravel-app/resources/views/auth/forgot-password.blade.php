<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <x-forms.form-group 
            label="{{ __('Email') }}" 
            name="email" 
            required 
            :error="$errors->first('email')">
            
            <x-text-input 
                id="email" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus
                :hasError="$errors->has('email')" />
        </x-forms.form-group>

        <div class="flex items-center justify-end mt-4">
            <x-ui.button type="submit" variant="primary">
                {{ __('Email Password Reset Link') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>

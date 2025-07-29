<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <x-forms.form-group 
            label="{{ __('Password') }}" 
            name="password" 
            required 
            :error="$errors->first('password')">
            
            <x-text-input 
                id="password" 
                type="password"
                name="password"
                required 
                autocomplete="current-password"
                :hasError="$errors->has('password')" />
        </x-forms.form-group>

        <div class="flex justify-end mt-4">
            <x-ui.button type="submit" variant="primary">
                {{ __('Confirm') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>

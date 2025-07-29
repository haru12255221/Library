<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            パスワード変更
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            アカウントのセキュリティを保つため、長くてランダムなパスワードを使用してください。
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <x-forms.form-group 
            label="現在のパスワード" 
            name="current_password" 
            required 
            :error="$errors->updatePassword->first('current_password')">
            
            <x-text-input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                autocomplete="current-password"
                :hasError="$errors->updatePassword->has('current_password')" />
        </x-forms.form-group>

        <x-forms.form-group 
            label="新しいパスワード" 
            name="password" 
            required 
            :error="$errors->updatePassword->first('password')">
            
            <x-text-input 
                id="update_password_password" 
                name="password" 
                type="password" 
                autocomplete="new-password"
                :hasError="$errors->updatePassword->has('password')" />
        </x-forms.form-group>

        <x-forms.form-group 
            label="パスワード確認" 
            name="password_confirmation" 
            required 
            :error="$errors->updatePassword->first('password_confirmation')">
            
            <x-text-input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                autocomplete="new-password"
                :hasError="$errors->updatePassword->has('password_confirmation')" />
        </x-forms.form-group>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit" variant="primary">保存</x-ui.button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >保存しました。</p>
            @endif
        </div>
    </form>
</section>

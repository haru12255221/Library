<section>
    <header>
        <h2 class="text-lg font-medium text-text-primary">
            プロフィール情報
        </h2>

        <p class="mt-1 text-sm text-text-secondary">
            アカウントのプロフィール情報とメールアドレスを更新してください。
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <x-forms.form-group 
            label="名前" 
            name="name" 
            required 
            :error="$errors->first('name')">
            
            <x-text-input 
                id="name" 
                name="name" 
                type="text" 
                :value="old('name', $user->name)" 
                required 
                autofocus 
                autocomplete="name"
                :hasError="$errors->has('name')" />
        </x-forms.form-group>

        <x-forms.form-group 
            label="メールアドレス" 
            name="email" 
            required 
            :error="$errors->first('email')">
            
            <x-text-input 
                id="email" 
                name="email" 
                type="email" 
                :value="old('email', $user->email)" 
                required 
                autocomplete="username"
                :hasError="$errors->has('email')" />

            
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-text-primary">
                        メールアドレスが未認証です。

                        <button form="send-verification" class="underline text-sm text-text-secondary hover:text-text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-300">
                            認証メールを再送信するにはここをクリックしてください。
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success">
                            新しい認証リンクがメールアドレスに送信されました。
                        </p>
                    @endif
                </div>
            @endif
        </x-forms.form-group>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit" variant="primary">保存</x-ui.button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-text-secondary"
                >保存しました。</p>
            @endif
        </div>
    </form>
</section>

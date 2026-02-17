<x-app-layout>
    <div class="flex flex-col items-center pt-8">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="flex justify-center mb-4">
                <a href="/">
                    <img src="{{ asset('images/Vector.png') }}" alt="アプリのアイコン" class="w-auto h-20">
                </a>
            </div>

            <h2 class="text-center text-2xl font-bold text-text-primary mb-4">パスワードリセット</h2>

            <p class="text-sm text-text-secondary mb-6">
                登録済みのメールアドレスを入力してください。パスワードリセット用のリンクをメールで送信します。
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-medium text-sm text-text-primary">メールアドレス</label>
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <a class="text-sm text-text-secondary hover:text-text-primary underline" href="{{ route('login') }}">
                        ログインに戻る
                    </a>

                    <x-ui.button type="submit" variant="primary">
                        リセットリンクを送信
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

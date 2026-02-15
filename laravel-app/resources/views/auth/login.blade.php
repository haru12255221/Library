<x-app-layout>
    <div class="flex flex-col items-center pt-8">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="flex justify-center mb-4">
                <a href="/">
                    <img src="{{ asset('images/Vector.png') }}" alt="アプリのアイコン" class="w-auto h-20">
                </a>
            </div>

            <h2 class="text-center text-2xl font-bold text-text-primary mb-6">ログイン</h2>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-medium text-sm text-text-primary">メールアドレス</label>
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-text-primary">パスワード</label>
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-border-neutral text-primary shadow-sm focus:ring-yellow-300" name="remember">
                        <span class="ms-2 text-sm text-text-secondary">ログイン状態を維持する</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    {{-- TODO: パスワードリセット機能（メール設定後に有効化） --}}

                    <button type="submit" class="ms-3 inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-hover focus:bg-primary-hover active:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        ログイン
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <div class="flex flex-col items-center pt-8">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="flex justify-center mb-4">
                <a href="/">
                    <img src="{{ asset('images/Vector.png') }}" alt="アプリのアイコン" class="w-auto h-20">
                </a>
            </div>

            <h2 class="text-center text-2xl font-bold text-lib-text-primary mb-6">ログイン</h2>

            <!-- Session Status -->
            @if (session('status'))
                <x-alert type="info">
                    {{ session('status') }}
                </x-alert>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700">メールアドレス</label>
                    <input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                    @error('email')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">パスワード</label>
                    <input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="password" name="password" required autocomplete="current-password" />
                    @error('password')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-primary shadow-sm focus:ring-primary" name="remember">
                        <span class="ms-2 text-sm text-lib-text-secondary">ログイン状態を維持する</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-lib-text-secondary hover:text-lib-text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lib-primary" href="{{ route('password.request') }}">
                            パスワードを忘れましたか？
                        </a>
                    @endif

                    <x-button type="submit" variant="primary" class="ms-3">
                        ログイン
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
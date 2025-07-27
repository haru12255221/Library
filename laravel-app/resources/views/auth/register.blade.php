<x-app-layout>
    <div class="flex flex-col items-center pt-8">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">

            <div class="flex justify-center mb-4">
                <a href="/">
                    <img src="{{ asset('images/Vector.png') }}" alt="アプリのアイコン" class="w-auto h-20">
                </a>
            </div>

            <h2 class="text-center text-2xl font-bold text-lib-text-primary mb-6">新規登録</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block font-medium text-sm text-gray-700">名前</label>
                    <input id="name" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                    @error('name')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <label for="email" class="block font-medium text-sm text-gray-700">メールアドレス</label>
                    <input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                    @error('email')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">パスワード</label>
                    <input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="password" name="password" required autocomplete="new-password" />
                    @error('password')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700">パスワード（確認用）</label>
                    <input id="password_confirmation" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-lib-primary focus:ring-lib-primary" type="password" name="password_confirmation" required autocomplete="new-password" />
                    @error('password_confirmation')
                        <p class="text-lib-accent text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-lib-text-secondary hover:text-lib-text-primary rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lib-primary" href="{{ route('login') }}">
                        すでに登録済みの方はこちら
                    </a>

                    <x-button type="submit" variant="primary" class="ms-4">
                        登録する
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
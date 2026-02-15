<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-background flex flex-col overflow-x-hidden w-full">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="py-8 flex-1 w-full">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-border-light mt-auto w-full">
                <div class="max-w-7xl mx-auto px-4 py-6 w-full">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-text-secondary w-full">
                        <p>&copy; {{ date('Y') }} 学校図書管理システム「本見れたり、借りれたり」</p>
                        <div class="flex flex-wrap items-center gap-4">
                            <a href="{{ route('books.index') }}" class="hover:text-text-primary hover:underline transition-colors">書籍一覧</a>
                            @auth
                                <a href="{{ route('loans.my') }}" class="hover:text-text-primary hover:underline transition-colors">マイページ</a>
                            @endauth
                            <a href="{{ route('legal.terms') }}" class="hover:text-text-primary hover:underline transition-colors">利用規約</a>
                            <a href="{{ route('legal.privacy') }}" class="hover:text-text-primary hover:underline transition-colors">プライバシーポリシー</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>

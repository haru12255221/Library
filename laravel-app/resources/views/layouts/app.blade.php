<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-background flex flex-col">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="py-8 flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-border-light mt-auto">
                <div class="max-w-7xl mx-auto px-4 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-text-secondary">
                        <p>&copy; {{ date('Y') }} 学校図書管理システム「本見れたり、借りれたり」</p>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('books.index') }}" class="hover:text-primary transition-colors">書籍一覧</a>
                            @auth
                                <a href="{{ route('loans.my') }}" class="hover:text-primary transition-colors">マイページ</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-primary leading-tight">
            プロフィール
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-ui.card padding="lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-ui.card>

            <x-ui.card padding="lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-ui.card>

            <x-ui.card padding="lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>

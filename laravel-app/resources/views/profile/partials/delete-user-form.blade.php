<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            アカウント削除
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            アカウントを削除すると、すべてのリソースとデータが完全に削除されます。アカウントを削除する前に、保持したいデータや情報をダウンロードしてください。
        </p>
    </header>

    <x-ui.button
        type="button"
        variant="danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >アカウントを削除</x-ui.button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                本当にアカウントを削除しますか？
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                アカウントを削除すると、すべてのリソースとデータが完全に削除されます。アカウントの削除を確認するため、パスワードを入力してください。
            </p>

            <div class="mt-6">
                <x-forms.form-group 
                    name="password" 
                    :error="$errors->userDeletion->first('password')">
                    
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="w-3/4"
                        placeholder="パスワード"
                        :hasError="$errors->userDeletion->has('password')"
                    />
                </x-forms.form-group>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    x-on:click="$dispatch('close')"
                >
                    キャンセル
                </x-ui.button>

                <x-ui.button type="submit" variant="danger">
                    アカウントを削除
                </x-ui.button>
            </div>
        </form>
    </x-modal>
</section>

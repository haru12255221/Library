<x-app-layout>
    <div class="min-h-screen flex flex-col items-center justify-start bg-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 tracking-tight">
                    書籍登録フォーム
                </h2>
                <p class="mt-2 text-center text-lg text-gray-500">
                    蔵書をデジタル管理しましょう
                </p>
            </div>
            <form class="mt-8 space-y-6" action="{{ route('books.store') }}" method="POST">
                @csrf
                <div class="rounded-md shadow-sm">
                    <div class="mb-6">
                        <label for="title" class="block text-lg font-medium text-gray-700 mb-2">タイトル</label>
                        <input id="title" name="title" type="text" required autofocus class="appearance-none rounded w-full px-3 py-4 border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-800 focus:border-cyan-800 text-xl" placeholder="例：吾輩は猫である">
                    </div>
                    <div class="mb-6">
                        <label for="author" class="block text-lg font-medium text-gray-700 mb-2">著者</label>
                        <input id="author" name="author" type="text" required class="appearance-none rounded w-full px-3 py-4 border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-800 focus:border-cyan-800 text-xl" placeholder="例：夏目漱石">
                    </div>
                    <div class="mb-6">
                        <label for="isbn" class="block text-lg font-medium text-gray-700 mb-2">ISBN</label>
                        <input id="isbn" name="isbn" type="text" required class="appearance-none rounded w-full px-3 py-4 border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-800 focus:border-cyan-800 text-xl" placeholder="例：978-4-00-310101-8">
                    </div>
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-xl font-bold rounded bg-cyan-800 text-white hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-800 transition">
                        登録する
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

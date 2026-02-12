<x-app-layout>
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-2xl font-bold text-text-primary mb-6">プライバシーポリシー</h1>
            <p class="text-sm text-text-secondary mb-8">最終更新日: 2026年2月13日</p>

            <div class="prose max-w-none text-text-primary space-y-6">
                <section>
                    <h2 class="text-lg font-semibold mb-2">1. 収集する情報</h2>
                    <p class="text-sm leading-relaxed mb-2">本サービスでは、以下の情報を収集します。</p>
                    <ul class="text-sm leading-relaxed list-disc pl-5 space-y-1">
                        <li><strong>アカウント情報</strong>: 名前、メールアドレス、パスワード（暗号化して保存）</li>
                        <li><strong>利用情報</strong>: 書籍の貸出・返却の記録</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">2. 情報の利用目的</h2>
                    <p class="text-sm leading-relaxed mb-2">収集した情報は、以下の目的でのみ利用します。</p>
                    <ul class="text-sm leading-relaxed list-disc pl-5 space-y-1">
                        <li>ユーザー認証とアカウント管理</li>
                        <li>書籍の貸出・返却の管理</li>
                        <li>返却期限の管理と延滞の検知</li>
                        <li>サービスの運営・改善</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">3. 情報の保管</h2>
                    <ul class="text-sm leading-relaxed list-disc pl-5 space-y-1">
                        <li>データはNeon（PostgreSQL）に暗号化通信（SSL）で保管されます</li>
                        <li>パスワードはbcryptでハッシュ化され、平文では保存されません</li>
                        <li>データベースは7日間の自動バックアップが行われます</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">4. 第三者への提供</h2>
                    <p class="text-sm leading-relaxed">収集した個人情報を第三者に提供・販売することはありません。ただし、法令に基づく開示請求があった場合はこの限りではありません。</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">5. Cookieの使用</h2>
                    <p class="text-sm leading-relaxed">本サービスでは、ログイン状態の維持のためにCookieを使用しています。ブラウザの設定でCookieを無効にすることができますが、その場合ログイン機能が利用できなくなります。</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">6. アカウントの削除</h2>
                    <p class="text-sm leading-relaxed">アカウントの削除を希望する場合は、管理者にお問い合わせください。アカウント削除後、関連する個人情報はデータベースから削除されます。</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">7. ポリシーの変更</h2>
                    <p class="text-sm leading-relaxed">本ポリシーは必要に応じて変更することがあります。変更後のポリシーは本ページに掲載した時点で効力を生じます。</p>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

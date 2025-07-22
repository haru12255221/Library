# 図書館管理システム - 技術設計書

## 概要

Laravel 11.xをベースとした図書館管理システムの技術設計書です。既存のLaravel Breezeによる認証機能を活用し、書籍管理機能を段階的に実装します。

## アーキテクチャ

### 技術スタック
- **バックエンド**: Laravel 11.x (PHP 8.2+)
- **データベース**: MySQL 8.0
- **フロントエンド**: Blade テンプレート + Tailwind CSS + Alpine.js
- **認証**: Laravel Breeze
- **開発環境**: Docker (Laravel Sail)

### MVC構成
```
app/
├── Http/Controllers/
│   ├── BookController.php (書籍管理)
│   └── ProfileController.php (既存)
├── Models/
│   ├── Book.php (書籍モデル)
│   └── User.php (既存、ロール機能拡張)
└── Middleware/
    └── AdminMiddleware.php (管理者権限チェック)

resources/views/
├── books/
│   ├── index.blade.php (一覧・検索)
│   ├── create.blade.php (既存)
│   └── show.blade.php (詳細表示)
└── layouts/
    └── app.blade.php (既存レイアウト)
```

## コンポーネントとインターフェース

### 1. データベース設計

#### usersテーブル（既存拡張）
```sql
users (
    id: bigint PRIMARY KEY,
    name: varchar(255),
    email: varchar(255) UNIQUE,
    password: varchar(255),
    role: tinyint DEFAULT 2, -- 1:管理者, 2:一般ユーザー
    created_at: timestamp,
    updated_at: timestamp
)
```

#### booksテーブル（既存）
```sql
books (
    id: bigint PRIMARY KEY,
    title: varchar(255) NOT NULL,
    author: varchar(255) NOT NULL,
    isbn: varchar(20) UNIQUE NOT NULL,
    created_at: timestamp,
    updated_at: timestamp
)
```

### 2. コントローラー設計

#### BookController
```php
class BookController extends Controller
{
    // 一覧・検索機能
    public function index(Request $request)
    // 登録フォーム表示（管理者のみ）
    public function create()
    // 書籍登録処理（管理者のみ）
    public function store(Request $request)
    // 書籍詳細表示
    public function show(Book $book)
    // ISBN自動入力API（管理者のみ）
    public function fetchByIsbn(Request $request)
}
```

### 3. ミドルウェア設計

#### AdminMiddleware
```php
class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (auth()->user()->role !== 1) {
            abort(403, '管理者権限が必要です');
        }
        return $next($request);
    }
}
```

### 4. フロントエンド設計

#### デザインシステム
- **カラーパレット**: 
  - **プライマリ**: `#295d72` (ダークティール) - メインカラー、ヘッダー、重要なボタン
  - **セカンダリ**: `#4f4f4f` (ダークグレー) - テキスト、サブタイトル
  - **アクセント**: `#ec652b` (オレンジ) - 検索ボタン、アクション要素
  - **ホバー**: `#3a7a94` (ライトティール) - ボタンホバー状態
  - **背景**: `#f8f9fa` (ライトグレー) - ページ背景
  - **カード**: `#ffffff` (ホワイト) - カード、フォーム背景
  - **アクセントライト**: `#f4a261` (ライトオレンジ) - ホバー、フォーカス状態
- **タイポグラフィ**: 
  - 見出し: text-3xl font-extrabold
  - 本文: text-lg
  - フォーム: text-xl
- **コンポーネント**:
  - 検索フォーム: 上部固定、リアルタイム検索対応
  - 書籍カード: グリッドレイアウト
  - ボタン: 統一されたスタイル

#### レスポンシブ対応
- モバイル: 1カラムレイアウト
- タブレット: 2カラムレイアウト  
- デスクトップ: 3カラムレイアウト

## データモデル

### Book モデル
```php
class Book extends Model
{
    protected $fillable = ['title', 'author', 'isbn'];
    
    // 検索スコープ
    public function scopeSearch($query, $keyword)
    {
        return $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
    }
    
    // バリデーションルール
    public static function validationRules()
    {
        return [
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|unique:books|regex:/^[0-9\-]+$/',
        ];
    }
}
```

### User モデル（拡張）
```php
class User extends Authenticatable
{
    // 管理者判定
    public function isAdmin()
    {
        return $this->role === 1;
    }
    
    // 一般ユーザー判定
    public function isUser()
    {
        return $this->role === 2;
    }
}
```

## エラーハンドリング

### バリデーションエラー
- フォーム送信時のリアルタイムバリデーション
- 日本語エラーメッセージの表示
- フィールド単位でのエラー表示

### システムエラー
- 403エラー: 権限不足時の適切なメッセージ
- 404エラー: 存在しない書籍へのアクセス
- 500エラー: データベース接続エラー等

### API エラー（ISBN自動入力）
- ネットワークエラー時のフォールバック
- APIレスポンス形式エラーの処理
- レート制限対応

## テスト戦略

### 単体テスト
- Book モデルのバリデーション
- 検索機能のスコープ
- ユーザーロール判定

### 機能テスト
- 書籍登録フロー
- 検索機能
- 権限チェック

### ブラウザテスト
- レスポンシブデザイン
- JavaScript機能
- フォーム操作

## セキュリティ考慮事項

### 認証・認可
- Laravel Breezeによる標準的な認証
- ミドルウェアによる管理者権限チェック
- CSRF保護（Laravel標準）

### データ保護
- SQLインジェクション対策（Eloquent ORM使用）
- XSS対策（Blade テンプレートエスケープ）
- 入力値サニタイゼーション

### API セキュリティ
- 外部API呼び出し時のタイムアウト設定
- レスポンスデータの検証
- APIキーの環境変数管理

## パフォーマンス考慮事項

### データベース
- ISBN フィールドにインデックス設定
- 検索クエリの最適化
- ページネーション実装（将来的）

### フロントエンド
- Tailwind CSS の本番ビルド最適化
- Alpine.js による軽量なJavaScript
- 画像最適化（将来的な書籍カバー機能）

## 開発フェーズ

### フェーズ1: 検索機能実装
1. books/index.blade.php の作成
2. BookController::index の検索機能追加
3. 検索フォームのスタイリング

### フェーズ2: 管理者権限実装
1. AdminMiddleware の作成
2. ルート保護の設定
3. 権限チェックの実装

### フェーズ3: ISBN自動入力機能
1. 外部API統合
2. JavaScript による非同期処理
3. エラーハンドリング実装

### フェーズ4: UI/UX改善
1. レスポンシブデザイン調整
2. アクセシビリティ対応
3. パフォーマンス最適化
# Bladeコンポーネントライブラリ

図書館管理システムで使用する統一されたBladeコンポーネントライブラリです。

## UIコンポーネント

### ボタン (`ui/button.blade.php`)

統一されたボタンコンポーネント。

**プロパティ:**
- `variant` (string): ボタンの種類 - `primary`, `success`, `danger`, `secondary` (デフォルト: `primary`)
- `size` (string): ボタンのサイズ - `sm`, `md`, `lg` (デフォルト: `md`)
- `disabled` (boolean): 無効状態 (デフォルト: `false`)
- `loading` (boolean): ローディング状態 (デフォルト: `false`)
- `href` (string): リンクボタンの場合のURL (オプション)
- `type` (string): ボタンタイプ (デフォルト: `button`)

**使用例:**
```php
<!-- 基本的なボタン -->
<x-ui.button variant="primary">保存</x-ui.button>

<!-- 大きな成功ボタン -->
<x-ui.button variant="success" size="lg">完了</x-ui.button>

<!-- ローディング状態のボタン -->
<x-ui.button :loading="$isSubmitting">送信中...</x-ui.button>

<!-- リンクボタン -->
<x-ui.button href="/books" variant="secondary">書籍一覧</x-ui.button>
```

### カード (`ui/card.blade.php`)

コンテンツをカードレイアウトで表示するコンポーネント。

**プロパティ:**
- `padding` (string): パディングサイズ - `none`, `sm`, `default`, `lg` (デフォルト: `default`)
- `shadow` (string): シャドウサイズ - `none`, `sm`, `default`, `lg`, `xl` (デフォルト: `default`)
- `border` (boolean): 境界線の表示 (デフォルト: `true`)

**スロット:**
- `header`: カードヘッダー (オプション)
- `footer`: カードフッター (オプション)
- デフォルトスロット: メインコンテンツ

**使用例:**
```php
<!-- 基本的なカード -->
<x-ui.card>
    <p>カードの内容</p>
</x-ui.card>

<!-- ヘッダーとフッター付きカード -->
<x-ui.card padding="lg">
    <x-slot name="header">
        <h2>タイトル</h2>
    </x-slot>
    
    <p>メインコンテンツ</p>
    
    <x-slot name="footer">
        <x-ui.button>アクション</x-ui.button>
    </x-slot>
</x-ui.card>
```

### アラート (`ui/alert.blade.php`)

メッセージやアラートを表示するコンポーネント。

**プロパティ:**
- `type` (string): アラートタイプ - `success`, `error`, `warning`, `info` (デフォルト: `info`)
- `dismissible` (boolean): 閉じるボタンの表示 (デフォルト: `false`)
- `icon` (boolean): アイコンの表示 (デフォルト: `true`)
- `title` (string): タイトル (オプション)

**使用例:**
```php
<!-- 成功メッセージ -->
<x-ui.alert type="success">
    操作が完了しました
</x-ui.alert>

<!-- 閉じるボタン付きエラーメッセージ -->
<x-ui.alert type="error" dismissible title="エラーが発生しました">
    入力内容を確認してください
</x-ui.alert>
```

### ローディング (`ui/loading.blade.php`)

ローディング状態を表示するコンポーネント。

**プロパティ:**
- `type` (string): ローディングタイプ - `spinner`, `dots`, `skeleton` (デフォルト: `spinner`)
- `size` (string): サイズ - `sm`, `md`, `lg`, `xl` (デフォルト: `md`)
- `text` (string): ローディングテキスト (オプション)
- `color` (string): 色 - `primary`, `white`, `gray` (デフォルト: `primary`)

**使用例:**
```php
<!-- スピナー -->
<x-ui.loading type="spinner" text="読み込み中..." />

<!-- ドット -->
<x-ui.loading type="dots" size="lg" />

<!-- スケルトン -->
<x-ui.loading type="skeleton" />
```

## フォームコンポーネント

### フォームグループ (`forms/form-group.blade.php`)

ラベル、入力欄、エラーメッセージを統一して表示するコンポーネント。

**プロパティ:**
- `label` (string): ラベルテキスト (オプション)
- `name` (string): フィールド名 (オプション)
- `required` (boolean): 必須項目マーク (デフォルト: `false`)
- `error` (string): エラーメッセージ (オプション)
- `help` (string): ヘルプテキスト (オプション)
- `id` (string): フィールドID (オプション、nameから自動生成)

**使用例:**
```php
<x-forms.form-group 
    label="メールアドレス" 
    name="email" 
    required 
    :error="$errors->first('email')"
    help="有効なメールアドレスを入力してください">
    
    <x-text-input type="email" name="email" id="email" />
</x-forms.form-group>
```

### バリデーションエラー (`forms/validation-error.blade.php`)

バリデーションエラーメッセージを表示するコンポーネント。

**プロパティ:**
- `messages` (array): エラーメッセージの配列
- `field` (string): フィールド名 (オプション)

**使用例:**
```php
<x-forms.validation-error :messages="$errors->get('title')" field="title" />
```

## ナビゲーションコンポーネント

### パンくずナビ (`navigation/breadcrumb.blade.php`)

パンくずナビゲーションを表示するコンポーネント。

**プロパティ:**
- `items` (array): パンくず項目の配列

**項目の構造:**
- `label` (string): 表示テキスト
- `url` (string): リンクURL (最後の項目以外)
- `icon` (string): SVGパス (オプション)

**使用例:**
```php
<x-navigation.breadcrumb :items="[
    ['label' => 'ホーム', 'url' => '/', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'],
    ['label' => '書籍一覧', 'url' => route('books.index')],
    ['label' => '書籍詳細']
]" />
```

## 既存コンポーネント（統一済み）

### プライマリボタン (`primary-button.blade.php`)
新しい`ui/button.blade.php`を使用するように更新済み。

### デンジャーボタン (`danger-button.blade.php`)
新しい`ui/button.blade.php`を使用するように更新済み。

### テキスト入力 (`text-input.blade.php`)
拡張済み（type、hasErrorプロパティ追加）。

### 入力エラー (`input-error.blade.php`)
アクセシビリティ向上済み（アイコン、role="alert"追加）。

### 認証セッション状態 (`auth-session-status.blade.php`)
新しい`ui/alert.blade.php`を使用するように更新済み。

## アクセシビリティ機能

すべてのコンポーネントは以下のアクセシビリティ機能を含んでいます：

- **ARIA属性**: 適切なrole、aria-label、aria-describedby等
- **キーボードナビゲーション**: Tab、Enter、Escape等のサポート
- **スクリーンリーダー対応**: セマンティックHTMLとARIA属性
- **フォーカス管理**: 明確なフォーカスリング
- **カラーコントラスト**: WCAG準拠のカラーパレット

## レスポンシブデザイン

すべてのコンポーネントはモバイルファーストで設計されています：

- **ブレークポイント**: sm (640px), md (768px), lg (1024px), xl (1280px)
- **タッチターゲット**: 最小44px以上
- **フレキシブルレイアウト**: Flexbox/Gridを活用

## カスタマイズ

### カラーパレット
`tailwind.config.js`で定義されたカスタムカラー：

```javascript
colors: {
    primary: '#3d7ca2',
    success: '#669C6F',
    danger: '#e3595b',
    'primary-hover': '#2a5a7a',
    'success-hover': '#c4d470',
    'danger-hover': '#d63d3f',
    background: '#f8f9fa',
    'header-bg': '#ffffff',
    'text-primary': '#4f4f4f',
    'text-secondary': '#6b7280',
    'text-light': '#9ca3af',
    'border-neutral': '#9ca3af',
    'border-light': '#d1d5db',
}
```

### 新しいコンポーネントの追加

1. 適切なディレクトリに配置 (`ui/`, `forms/`, `navigation/`)
2. プロパティとスロットを定義
3. アクセシビリティ属性を追加
4. レスポンシブ対応
5. このREADMEに使用方法を追記

## トラブルシューティング

### よくある問題

**Q: コンポーネントが表示されない**
A: ファイルパスとコンポーネント名を確認してください。`x-ui.button`は`resources/views/components/ui/button.blade.php`に対応します。

**Q: スタイルが適用されない**
A: Tailwind CSSのクラスが正しく読み込まれているか確認してください。

**Q: Alpine.jsが動作しない**
A: Alpine.jsが読み込まれているか、x-dataディレクティブが正しく設定されているか確認してください。
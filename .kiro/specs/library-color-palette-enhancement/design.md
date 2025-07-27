# 図書館カラーパレット改善 - 設計書

## 概要

既存の図書館管理システムのカラーパレットを、指定された3色（コーラルレッド #E3595B、スチールブルー #3D7CA3、自然な緑 #669C6F）に変更し、保守性の高いデザインシステムを構築します。

## アーキテクチャ

### デザインシステムの構造

```
tailwind.config.js (カラーパレット設定)
resources/
├── css/
│   └── app.css (Tailwindディレクティブ)
└── views/
    └── components/ (Bladeコンポーネント)
        ├── button.blade.php
        ├── card.blade.php
        ├── alert.blade.php
        └── form-input.blade.php
```

### Tailwind設定による色管理

```javascript
// tailwind.config.js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Library System Colors
        'lib-primary': {
          DEFAULT: '#3D7CA3',
          hover: '#2A5A7A',
          light: '#E6F0F7',
        },
        'lib-secondary': {
          DEFAULT: '#669C6F',
          hover: '#4A7A4F',
          light: '#E8F2E9',
        },
        'lib-accent': {
          DEFAULT: '#E3595B',
          hover: '#C4434B',
          light: '#F8E6E7',
        },
        // Semantic Colors
        'lib-success': '#669C6F',
        'lib-error': '#E3595B',
        'lib-warning': '#F59E0B',
        'lib-info': '#3D7CA3',
      },
    },
  },
  plugins: [],
}
```

## コンポーネント設計

### 1. ボタンコンポーネント

#### Tailwindクラス設計
```php
<!-- resources/views/components/button.blade.php -->
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md'
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';

$variantClasses = [
    'primary' => 'bg-lib-primary text-white hover:bg-lib-primary-hover focus:ring-lib-primary',
    'secondary' => 'bg-lib-secondary text-white hover:bg-lib-secondary-hover focus:ring-lib-secondary',
    'danger' => 'bg-lib-accent text-white hover:bg-lib-accent-hover focus:ring-lib-accent',
    'outline' => 'border border-lib-primary text-lib-primary hover:bg-lib-primary hover:text-white',
];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-base',
    'lg' => 'px-6 py-3 text-lg',
];

$classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size];
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</button>
```

### 2. カードコンポーネント

#### Tailwindクラス設計
```php
<!-- resources/views/components/card.blade.php -->
@props([
    'hover' => true,
    'padding' => 'md'
])

@php
$baseClasses = 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden';
$hoverClasses = $hover ? 'transition-all duration-200 hover:shadow-md hover:-translate-y-0.5' : '';
$paddingClasses = [
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
];
@endphp

<div {{ $attributes->merge(['class' => $baseClasses . ' ' . $hoverClasses]) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            {{ $header }}
        </div>
    @endisset
    
    <div class="{{ $paddingClasses[$padding] }}">
        {{ $slot }}
    </div>
</div>
```

### 3. アラートコンポーネント

#### Tailwindクラス設計
```php
<!-- resources/views/components/alert.blade.php -->
@props([
    'type' => 'info',
    'dismissible' => false
])

@php
$baseClasses = 'px-4 py-3 rounded-md border flex items-center gap-2 mb-4';

$typeClasses = [
    'success' => 'bg-lib-secondary-light border-lib-secondary text-lib-secondary-hover',
    'error' => 'bg-lib-accent-light border-lib-accent text-lib-accent-hover',
    'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
    'info' => 'bg-lib-primary-light border-lib-primary text-lib-primary-hover',
];

$icons = [
    'success' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'error' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'warning' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
    'info' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
];
@endphp

<div {{ $attributes->merge(['class' => $baseClasses . ' ' . $typeClasses[$type]]) }}>
    {!! $icons[$type] !!}
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="ml-auto hover:opacity-75">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</div>
```

### 4. フォームコンポーネント

#### Tailwindクラス設計
```php
<!-- resources/views/components/form-input.blade.php -->
@props([
    'label' => null,
    'error' => null,
    'required' => false,
    'type' => 'text'
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-lib-accent">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        {{ $attributes->except(['class', 'label', 'error', 'required'])->merge([
            'class' => 'w-full px-3 py-2 border rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 ' . 
                      ($error ? 'border-lib-accent focus:border-lib-accent focus:ring-lib-accent' : 'border-gray-300 focus:border-lib-primary focus:ring-lib-primary')
        ]) }}
    >
    
    @if($error)
        <p class="text-lib-accent text-sm mt-1">{{ $error }}</p>
    @endif
</div>
```

## データモデル

### カラーパレット設定

```php
// config/design-system.php
return [
    'colors' => [
        'primary' => '#3D7CA3',
        'primary-hover' => '#2A5A7A',
        'secondary' => '#669C6F',
        'secondary-hover' => '#4A7A4F',
        'accent' => '#E3595B',
        'accent-hover' => '#C4434B',
    ],
    
    'spacing' => [
        'xs' => '0.25rem',
        'sm' => '0.5rem',
        'md' => '1rem',
        'lg' => '1.5rem',
        'xl' => '2rem',
    ],
    
    'border-radius' => [
        'sm' => '0.25rem',
        'md' => '0.5rem',
        'lg' => '0.75rem',
    ],
];
```

## インターフェース設計

### 1. ページレベルの変更

#### 書籍一覧ページ (books/index.blade.php)
- 検索ボタン: `btn btn-primary`
- 書籍登録ボタン: `btn btn-primary`
- 成功メッセージ: `alert alert-success`
- 利用可能状態: `text-success`
- 貸出中状態: `text-error`

#### 書籍登録ページ (books/create.blade.php)
- ISBN検索ボタン: `btn btn-primary`
- 登録ボタン: `btn btn-primary`
- キャンセルボタン: `btn btn-secondary`
- 成功メッセージ: `alert alert-success`
- エラーメッセージ: `alert alert-error`

#### 書籍詳細ページ (books/show.blade.php)
- 戻るリンク: `text-primary hover:text-primary-hover`
- 借りるボタン: `btn btn-primary`
- 利用可能状態: `text-success`
- 貸出中状態: `text-error`

#### マイページ (loans/my-loans.blade.php)
- 返却ボタン: `btn btn-primary`
- 成功メッセージ: `alert alert-success`
- 警告メッセージ: `alert alert-warning`

### 2. コンポーネントレベルの変更

#### ナビゲーション
- アクティブリンク: `text-primary`
- ホバー状態: `hover:text-primary-hover`

#### フォーム要素
- フォーカス状態: `focus:border-primary focus:ring-primary`
- エラー状態: `border-error text-error`

## エラーハンドリング

### Tailwind設定のフォールバック
```javascript
// tailwind.config.js - フォールバック色の設定
module.exports = {
  theme: {
    extend: {
      colors: {
        'lib-primary': {
          DEFAULT: '#3D7CA3',
          50: '#F0F7FF',
          100: '#E6F0F7',
          // ... 他のシェード
          900: '#1A2E3A',
        },
      },
    },
  },
  // セーフリスト（Purgeで削除されないクラス）
  safelist: [
    'bg-lib-primary',
    'bg-lib-secondary', 
    'bg-lib-accent',
    'text-lib-primary',
    'text-lib-secondary',
    'text-lib-accent',
    'border-lib-primary',
    'border-lib-secondary',
    'border-lib-accent',
  ],
}
```

### JavaScript による動的スタイル適用
```javascript
// 動的にカラーパレットを変更する場合
function updateColorPalette(colors) {
  const root = document.documentElement;
  Object.entries(colors).forEach(([key, value]) => {
    root.style.setProperty(`--color-${key}`, value);
  });
}
```

## テスト戦略

### 1. 視覚的回帰テスト
- 各ページのスクリーンショット比較
- 異なるブラウザでの表示確認

### 2. アクセシビリティテスト
- コントラスト比の確認 (WCAG 2.1 AA準拠)
- キーボードナビゲーションの確認
- スクリーンリーダーでの読み上げ確認

### 3. レスポンシブテスト
- モバイル、タブレット、デスクトップでの表示確認
- 各ブレークポイントでのレイアウト確認

## パフォーマンス考慮事項

### Tailwind最適化
- 未使用クラスの自動削除（Purge CSS）
- カスタムカラーの効率的な定義
- JITモードの活用

### ファイル構成
- tailwind.config.jsでの一元管理
- Bladeコンポーネントでの再利用
- 必要に応じたカスタムCSSの最小化

## 移行戦略

### フェーズ1: 基盤構築
1. tailwind.config.jsでのカラーパレット定義
2. Bladeコンポーネントの作成
3. 既存のインラインスタイル調査

### フェーズ2: ページ別適用
1. 書籍一覧ページの更新
2. 書籍登録ページの更新
3. 書籍詳細ページの更新
4. マイページの更新

### フェーズ3: 最適化
1. 未使用スタイルの削除
2. パフォーマンス最適化
3. アクセシビリティ確認

## 保守性の確保

### 命名規則
- BEM記法の採用: `.component__element--modifier`
- 意味のあるクラス名: `.btn-primary` vs `.blue-button`
- 一貫したプレフィックス: `.lib-` (library system)

### ドキュメント化
- スタイルガイドの作成
- コンポーネントカタログの整備
- 使用例とベストプラクティスの記載

### バージョン管理
- デザインシステムのバージョニング
- 変更履歴の記録
- 後方互換性の考慮
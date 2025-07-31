# レスポンシブデザイン設計書

## 概要

図書館管理システムの画面はみ出し問題を解決し、スマートフォン、タブレット、デスクトップの各デバイスで最適な表示・操作体験を提供するレスポンシブデザインシステムを設計します。

## アーキテクチャ

### システム構成

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend Layer                           │
├─────────────────────────────────────────────────────────────┤
│  Responsive Framework                                       │
│  ├─ Tailwind CSS（レスポンシブユーティリティ）                │
│  ├─ Alpine.js（インタラクティブ機能）                        │
│  └─ Custom CSS（必要に応じて）                              │
├─────────────────────────────────────────────────────────────┤
│  Component Layer                                            │
│  ├─ Layout Components（レイアウト）                         │
│  ├─ UI Components（再利用可能なUI）                         │
│  └─ Page Components（ページ固有）                          │
├─────────────────────────────────────────────────────────────┤
│  Laravel Blade Templates                                   │
│  ├─ layouts/app.blade.php（基本レイアウト）                 │
│  ├─ layouts/navigation.blade.php（ナビゲーション）          │
│  └─ Individual Pages（各ページ）                           │
└─────────────────────────────────────────────────────────────┘
```

### レスポンシブブレークポイント戦略

```
Mobile First Approach:
┌─────────────┬─────────────┬─────────────┬─────────────┐
│   Mobile    │   Tablet    │  Desktop    │ Large Screen│
│   0-639px   │  640-1023px │ 1024-1279px │  1280px+    │
├─────────────┼─────────────┼─────────────┼─────────────┤
│ Base Styles │ sm: prefix  │ lg: prefix  │ xl: prefix  │
│ 単列レイアウト │ 2列レイアウト │ 3列レイアウト │ 4列レイアウト │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

## コンポーネントとインターフェース

### 1. Container System（コンテナシステム）

**責任**: 画面幅に応じた適切なコンテンツ幅の管理

```html
<!-- 基本コンテナ -->
<div class="w-full sm:max-w-2xl md:max-w-4xl lg:max-w-6xl xl:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- コンテンツ -->
</div>
```

**ブレークポイント別動作**:
- **Mobile (0-639px)**: `w-full px-4` → 全幅、16px余白
- **Tablet (640-1023px)**: `sm:max-w-2xl sm:px-6` → 最大672px、24px余白
- **Desktop (1024-1279px)**: `lg:max-w-6xl lg:px-8` → 最大1152px、32px余白
- **Large (1280px+)**: `xl:max-w-7xl` → 最大1280px、32px余白

### 2. Navigation System（ナビゲーションシステム）

**責任**: デバイスに応じたナビゲーション表示の切り替え

```html
<!-- デスクトップナビゲーション -->
<nav class="hidden md:flex items-baseline gap-6 text-sm font-medium">
    <!-- メニュー項目 -->
</nav>

<!-- モバイルハンバーガーメニュー -->
<div class="md:hidden flex items-center">
    <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
        <!-- ハンバーガーアイコン -->
    </button>
</div>
```

**改善点**:
- ロゴサイズの最適化: `h-20` → `h-12 sm:h-16 lg:h-20`
- タッチターゲットサイズ: 最小44px×44px確保

### 3. Card System（カードシステム）

**責任**: 書籍情報の表示レイアウト管理

```html
<!-- レスポンシブ書籍カード -->
<div class="grid gap-4 sm:gap-6">
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <div class="flex gap-3 sm:gap-4">
            <!-- 表紙画像 -->
            <div class="flex-shrink-0">
                <img class="w-12 h-16 sm:w-16 sm:h-20 object-cover rounded">
            </div>
            <!-- 書籍情報 -->
            <div class="flex-1 min-w-0">
                <h3 class="text-base sm:text-lg font-semibold truncate">タイトル</h3>
                <p class="text-sm sm:text-base text-gray-600">著者情報</p>
            </div>
        </div>
    </div>
</div>
```

### 4. Form System（フォームシステム）

**責任**: 入力フォームのレスポンシブ対応

```html
<!-- レスポンシブフォーム -->
<form class="space-y-4">
    <!-- 入力フィールド -->
    <input class="w-full px-3 py-2 min-h-[44px] border rounded-md">
    
    <!-- ボタングループ -->
    <div class="flex flex-col sm:flex-row gap-3">
        <button class="w-full sm:w-auto min-h-[44px] px-4 py-2">検索</button>
        <button class="w-full sm:w-auto min-h-[44px] px-4 py-2">リセット</button>
    </div>
</form>
```

## データモデル

### Responsive Breakpoint Configuration

```javascript
// tailwind.config.js
const breakpoints = {
  sm: '640px',   // タブレット小
  md: '768px',   // タブレット
  lg: '1024px',  // デスクトップ小
  xl: '1280px',  // デスクトップ
  '2xl': '1536px' // 大画面
};
```

### Component Size Mapping

```javascript
const componentSizes = {
  // 表紙画像サイズ
  bookThumbnail: {
    mobile: 'w-12 h-16',
    tablet: 'sm:w-16 sm:h-20',
    desktop: 'lg:w-20 lg:h-24'
  },
  
  // テキストサイズ
  typography: {
    title: 'text-base sm:text-lg lg:text-xl',
    body: 'text-sm sm:text-base',
    caption: 'text-xs sm:text-sm'
  },
  
  // 余白サイズ
  spacing: {
    container: 'px-4 sm:px-6 lg:px-8',
    card: 'p-4 sm:p-6',
    gap: 'gap-3 sm:gap-4 lg:gap-6'
  }
};
```

## エラーハンドリング

### 1. レイアウト崩れの防止

```css
/* オーバーフロー制御 */
.container {
  @apply overflow-x-hidden;
}

/* 長いテキストの処理 */
.text-truncate {
  @apply truncate;
}

.text-clamp {
  @apply line-clamp-2;
}
```

### 2. 画像読み込みエラー

```html
<!-- フォールバック画像 -->
<img src="{{ $book->thumbnail_url }}" 
     alt="{{ $book->title }}の表紙"
     onerror="this.src='/images/no-image.png'"
     class="w-12 h-16 sm:w-16 sm:h-20 object-cover rounded">
```

### 3. JavaScript無効時の対応

```html
<!-- プログレッシブエンハンスメント -->
<noscript>
    <style>
        .js-only { display: none !important; }
        .no-js-show { display: block !important; }
    </style>
</noscript>
```

## テスト戦略

### 1. レスポンシブテスト

**ブレークポイントテスト**:
```javascript
// テスト対象画面サイズ
const testSizes = [
  { width: 375, height: 667, name: 'iPhone SE' },
  { width: 768, height: 1024, name: 'iPad' },
  { width: 1024, height: 768, name: 'Desktop Small' },
  { width: 1920, height: 1080, name: 'Desktop Large' }
];
```

**Visual Regression Testing**:
- 各ブレークポイントでのスクリーンショット比較
- レイアウト崩れの自動検出

### 2. パフォーマンステスト

**Core Web Vitals**:
- LCP (Largest Contentful Paint): 2.5秒以下
- FID (First Input Delay): 100ms以下
- CLS (Cumulative Layout Shift): 0.1以下

### 3. アクセシビリティテスト

**タッチターゲットサイズ**:
```javascript
// 最小タッチターゲットサイズチェック
const minTouchTarget = 44; // px
const buttons = document.querySelectorAll('button, a');
buttons.forEach(button => {
  const rect = button.getBoundingClientRect();
  if (rect.width < minTouchTarget || rect.height < minTouchTarget) {
    console.warn('Touch target too small:', button);
  }
});
```

## 実装詳細

### 1. CSS Grid vs Flexbox 使い分け

```css
/* 書籍一覧: CSS Grid */
.book-grid {
  @apply grid gap-4;
  @apply grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4;
}

/* カード内レイアウト: Flexbox */
.book-card {
  @apply flex gap-4;
}
```

### 2. 画像最適化

```html
<!-- レスポンシブ画像 -->
<img srcset="
  {{ $book->thumbnail_url }}?w=48 48w,
  {{ $book->thumbnail_url }}?w=64 64w,
  {{ $book->thumbnail_url }}?w=80 80w
" 
sizes="(max-width: 640px) 48px, (max-width: 1024px) 64px, 80px"
src="{{ $book->thumbnail_url }}"
class="w-12 h-16 sm:w-16 sm:h-20 object-cover rounded">
```

### 3. フォント最適化

```css
/* 可変フォントサイズ */
.responsive-text {
  font-size: clamp(0.875rem, 2.5vw, 1.125rem);
}

/* 行間調整 */
.responsive-leading {
  @apply leading-tight sm:leading-normal;
}
```

## セキュリティ考慮事項

### 1. XSS防止

```php
<!-- 安全なテキスト表示 -->
{{ $book->title }} <!-- 自動エスケープ -->

<!-- 画像URL検証 -->
@if(filter_var($book->thumbnail_url, FILTER_VALIDATE_URL))
    <img src="{{ $book->thumbnail_url }}">
@endif
```

### 2. CSP (Content Security Policy)

```html
<meta http-equiv="Content-Security-Policy" 
      content="img-src 'self' https:; script-src 'self' 'unsafe-inline';">
```

## パフォーマンス最適化

### 1. Critical CSS

```css
/* Above-the-fold CSS */
.critical {
  @apply container mx-auto px-4;
  @apply flex justify-between items-center;
}
```

### 2. Lazy Loading

```html
<!-- 画像遅延読み込み -->
<img loading="lazy" 
     src="{{ $book->thumbnail_url }}"
     class="w-12 h-16 sm:w-16 sm:h-20">
```

### 3. Resource Hints

```html
<!-- DNS prefetch -->
<link rel="dns-prefetch" href="//fonts.bunny.net">

<!-- Preload critical resources -->
<link rel="preload" href="/css/app.css" as="style">
```

## 移行戦略

### Phase 1: 基盤整備
1. コンテナシステムの統一
2. ブレークポイント戦略の確立
3. 基本コンポーネントの作成

### Phase 2: コンポーネント最適化
1. ナビゲーションの改善
2. カードシステムの最適化
3. フォームシステムの改善

### Phase 3: 詳細調整
1. タイポグラフィの最適化
2. 余白・間隔の調整
3. パフォーマンス最適化

### Phase 4: テスト・検証
1. 各デバイスでの動作確認
2. アクセシビリティテスト
3. パフォーマンステスト
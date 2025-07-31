# 設計書

## 概要

現在のアラートメッセージシステムをトーストメッセージシステムに変更する設計です。トーストメッセージは画面右上に表示され、自動的に消える通知システムを実装します。

## アーキテクチャ

### システム構成

```
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Application                      │
├─────────────────────────────────────────────────────────────┤
│  Controller Layer (既存のまま変更なし)                        │
│  ├─ LoanController (既存のsession flash messages)          │
│  ├─ BookController (既存のsession flash messages)          │
│  └─ Other Controllers                                       │
├─────────────────────────────────────────────────────────────┤
│  View Layer                                                 │
│  ├─ layouts/app.blade.php (Toast Container)                │
│  ├─ components/ui/toast.blade.php (Toast Component)        │
│  └─ Individual Pages (Toast Triggers)                      │
├─────────────────────────────────────────────────────────────┤
│  Frontend Layer                                             │
│  ├─ Alpine.js (State Management)                           │
│  ├─ Tailwind CSS (Styling)                                 │
│  └─ JavaScript (Toast Manager)                             │
└─────────────────────────────────────────────────────────────┘
```

### データフロー

### 処理の流れ

1. **ユーザー** → 「借りる」ボタンをクリック
2. **Controller** → 貸出処理を実行
3. **Session** → 「本を借りました！」メッセージを保存
4. **View** → 書籍一覧ページを表示
5. **ToastManager** → セッションメッセージを検出
6. **DOM** → 画面右上にトーストメッセージを表示
7. **自動処理** → 5秒後にトーストが消える

## コンポーネントとインターフェース

### 1. Toast Manager (JavaScript)

**責任**: トーストの生成、管理、削除

```javascript
class ToastManager {
    // トースト表示
    show(message, type = 'info', options = {})
    
    // トースト削除
    remove(toastId)
    
    // 全トースト削除
    clear()
    
    // セッションメッセージからトースト生成
    processSessionMessages()
}
```

### 2. Toast Component (Blade)

**ファイル**: `resources/views/components/ui/toast.blade.php`

**Props**:
- `type`: success, error, warning, info
- `message`: 表示メッセージ
- `dismissible`: 手動で閉じられるか
- `duration`: 自動消去時間（ミリ秒）

### 3. Toast Container (Layout)

**ファイル**: `resources/views/layouts/app.blade.php`に追加

**責任**: トーストの表示領域を提供

## データモデル

### Toast Object Structure

```javascript
{
    id: 'unique-id',           // ユニークID
    type: 'success',           // success, error, warning, info
    message: 'メッセージ内容',    // 表示メッセージ
    dismissible: true,         // 手動で閉じられるか
    duration: 5000,            // 自動消去時間（ms）
    timestamp: Date.now(),     // 作成時刻
    paused: false             // ホバー時の一時停止状態
}
```

### Session Flash Message Mapping

```php
// Laravel Session → Toast Type Mapping
'success' => 'success'
'error'   => 'error'
'warning' => 'warning'
'info'    => 'info'
```

## エラーハンドリング

### 1. JavaScript エラー

```javascript
// Toast Manager でのエラーハンドリング
try {
    this.show(message, type);
} catch (error) {
    console.error('Toast display error:', error);
    // フォールバック: コンソールにメッセージ表示
    console.log(`Fallback message: ${message}`);
}
```

### 2. DOM 操作エラー

```javascript
// DOM要素が存在しない場合の処理
const container = document.getElementById('toast-container');
if (!container) {
    console.warn('Toast container not found');
    return;
}
```

### 3. Alpine.js 統合エラー

```javascript
// Alpine.js が利用できない場合のフォールバック
if (typeof Alpine === 'undefined') {
    // バニラJSでの実装にフォールバック
    this.fallbackToVanillaJS();
}
```

## テスト戦略

### 1. ユニットテスト

**JavaScript (Jest)**:
- ToastManager クラスのメソッド
- トースト生成・削除ロジック
- タイマー機能

**PHP (PHPUnit)**:
- セッションフラッシュメッセージの生成
- コントローラーのリダイレクト処理

### 2. 統合テスト

**Browser Testing (Laravel Dusk)**:
- トースト表示の確認
- 自動消去の動作確認
- 手動削除の動作確認
- 複数トーストのスタック表示

### 3. アクセシビリティテスト

**ARIA属性テスト**:
- スクリーンリーダー対応
- キーボードナビゲーション
- フォーカス管理

## 実装詳細

### 1. CSS アニメーション

```css
/* トースト表示アニメーション */
@keyframes toast-slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* トースト消去アニメーション */
@keyframes toast-slide-out {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
```

### 2. Alpine.js 統合

```javascript
// Alpine.js store for toast management
Alpine.store('toasts', {
    items: [],
    
    add(toast) {
        this.items.push(toast);
    },
    
    remove(id) {
        this.items = this.items.filter(toast => toast.id !== id);
    }
});
```

### 3. Tailwind CSS クラス

```css
/* トーストコンテナ */
.toast-container {
    @apply fixed top-4 right-4 z-50 space-y-2;
}

/* トーストベース */
.toast {
    @apply max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto;
    @apply ring-1 ring-black ring-opacity-5 overflow-hidden;
}

/* タイプ別スタイル */
.toast-success {
    @apply border-l-4 border-green-400;
}

.toast-error {
    @apply border-l-4 border-red-400;
}

.toast-warning {
    @apply border-l-4 border-yellow-400;
}

.toast-info {
    @apply border-l-4 border-blue-400;
}
```

## セキュリティ考慮事項

### 1. XSS 防止

```php
// Blade テンプレートでのエスケープ
{{ $message }} // 自動エスケープ
{!! $message !!} // 使用禁止
```

### 2. CSRF 保護

```javascript
// AJAX リクエスト時のCSRFトークン
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
```

## パフォーマンス最適化

### 1. メモリ管理

```javascript
// 古いトーストの自動削除
const MAX_TOASTS = 5;
if (this.items.length > MAX_TOASTS) {
    this.items.shift(); // 最古のトーストを削除
}
```

### 2. DOM 操作の最適化

```javascript
// DocumentFragment を使用した効率的なDOM操作
const fragment = document.createDocumentFragment();
toasts.forEach(toast => {
    fragment.appendChild(createToastElement(toast));
});
container.appendChild(fragment);
```

## 移行戦略

### Phase 1: トーストシステム実装
1. Toast Manager JavaScript クラス作成
2. Toast Blade コンポーネント作成
3. レイアウトにトーストコンテナ追加

### Phase 2: 既存システムとの統合
1. セッションメッセージの自動変換
2. 既存アラートコンポーネントの段階的置き換え

### Phase 3: 最適化とテスト
1. パフォーマンス最適化
2. アクセシビリティ改善
3. 包括的テスト実装
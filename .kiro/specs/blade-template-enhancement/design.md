# デザイン文書

## 概要

図書館管理システムのBladeテンプレート改善プロジェクトは、現在のテンプレートシステムを統一し、ユーザビリティとアクセシビリティを向上させることを目的としています。既存のTailwind CSSベースのデザインシステムを拡張し、一貫性のあるコンポーネントライブラリを構築します。

## アーキテクチャ

### 現在の構造分析

**既存のレイアウトシステム:**
- `app.blade.php` - メインアプリケーションレイアウト（`bg-background`使用）
- `app-layout.blade.php` - 重複レイアウト（`bg-[#f8f9fa]`使用）
- `guest.blade.php` - ゲストユーザー向けレイアウト

**既存のコンポーネント:**
- フォーム要素: `text-input.blade.php`, `input-label.blade.php`, `input-error.blade.php`
- ボタン: `primary-button.blade.php`, `danger-button.blade.php`
- ナビゲーション: `dropdown.blade.php`, `dropdown-link.blade.php`
- 状態表示: `auth-session-status.blade.php`

### 改善されたアーキテクチャ

```
resources/views/
├── layouts/
│   ├── app.blade.php (統一されたメインレイアウト)
│   └── guest.blade.php (ゲストレイアウト)
├── components/
│   ├── ui/
│   │   ├── button.blade.php (統一ボタンコンポーネント)
│   │   ├── input.blade.php (統一入力コンポーネント)
│   │   ├── card.blade.php (カードコンポーネント)
│   │   ├── alert.blade.php (アラートコンポーネント)
│   │   └── loading.blade.php (ローディングコンポーネント)
│   ├── forms/
│   │   ├── form-group.blade.php (フォームグループ)
│   │   └── validation-error.blade.php (バリデーションエラー)
│   └── navigation/
│       ├── breadcrumb.blade.php (パンくずナビ)
│       └── pagination.blade.php (ページネーション)
└── partials/
    ├── success-message.blade.php (成功メッセージ)
    └── error-message.blade.php (エラーメッセージ)
```

## コンポーネントとインターフェース

### 1. デザインシステムの統一

**カラーパレット（tailwind.config.jsより）:**
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

**統一ボタンコンポーネント:**
```php
// components/ui/button.blade.php
@props([
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'loading' => false
])

@php
$variants = [
    'primary' => 'bg-primary hover:bg-primary-hover text-white',
    'success' => 'bg-success hover:bg-success-hover text-white',
    'danger' => 'bg-danger hover:bg-danger-hover text-white',
    'secondary' => 'bg-gray-500 hover:bg-gray-600 text-white'
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base'
];
@endphp

<button {{ $attributes->merge([
    'type' => 'button',
    'disabled' => $disabled || $loading,
    'class' => 'inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed ' . 
               $variants[$variant] . ' ' . $sizes[$size]
]) }}>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
```

### 2. アクセシビリティ向上

**フォーカス管理:**
- 全てのインタラクティブ要素に明確なフォーカスリング
- キーボードナビゲーション対応
- スクリーンリーダー対応のARIAラベル

**改善されたドロップダウンコンポーネント:**
```php
// components/ui/dropdown.blade.php
@props(['align' => 'right', 'width' => '48'])

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" @keydown.enter="open = ! open" @keydown.space.prevent="open = ! open" 
         tabindex="0" role="button" aria-haspopup="true" :aria-expanded="open">
        {{ $trigger }}
    </div>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $width === '48' ? 'w-48' : $width }} rounded-md shadow-lg {{ $align === 'left' ? 'left-0' : 'right-0' }}"
         role="menu" aria-orientation="vertical" @click="open = false"
         @keydown.escape.window="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
            {{ $content }}
        </div>
    </div>
</div>
```

### 3. レスポンシブデザイン改善

**ブレークポイント戦略:**
- Mobile First アプローチ
- sm: 640px (タブレット縦)
- md: 768px (タブレット横)
- lg: 1024px (デスクトップ)
- xl: 1280px (大画面)

**レスポンシブカードコンポーネント:**
```php
// components/ui/card.blade.php
@props(['padding' => 'default'])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8'
];
@endphp

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-lg shadow-md border border-gray-200 ' . $paddingClasses[$padding]
]) }}>
    {{ $slot }}
</div>
```

### 4. フォームとインタラクション改善

**統一フォームグループコンポーネント:**
```php
// components/forms/form-group.blade.php
@props([
    'label',
    'name',
    'required' => false,
    'error' => null,
    'help' => null
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-text-primary">
            {{ $label }}
            @if($required)
                <span class="text-danger ml-1">*</span>
            @endif
        </label>
    @endif
    
    <div>
        {{ $slot }}
    </div>
    
    @if($error)
        <p class="text-sm text-danger" role="alert">{{ $error }}</p>
    @endif
    
    @if($help)
        <p class="text-sm text-text-secondary">{{ $help }}</p>
    @endif
</div>
```

**リアルタイムバリデーション:**
```javascript
// resources/js/form-validation.js
class FormValidator {
    constructor(form) {
        this.form = form;
        this.rules = {};
        this.init();
    }
    
    init() {
        this.form.addEventListener('input', this.handleInput.bind(this));
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }
    
    handleInput(event) {
        const field = event.target;
        if (this.rules[field.name]) {
            this.validateField(field);
        }
    }
    
    validateField(field) {
        const rule = this.rules[field.name];
        const isValid = rule.validator(field.value);
        this.showFieldFeedback(field, isValid, rule.message);
    }
    
    showFieldFeedback(field, isValid, message) {
        const errorElement = field.parentNode.querySelector('.validation-error');
        if (errorElement) {
            errorElement.textContent = isValid ? '' : message;
            errorElement.classList.toggle('hidden', isValid);
        }
        
        field.classList.toggle('border-danger', !isValid);
        field.classList.toggle('border-success', isValid && field.value);
    }
}
```

## データモデル

### コンポーネントプロパティ

**ボタンコンポーネント:**
```php
[
    'variant' => 'primary|success|danger|secondary',
    'size' => 'sm|md|lg',
    'disabled' => boolean,
    'loading' => boolean,
    'icon' => string (SVGアイコン),
    'href' => string (リンクボタンの場合)
]
```

**入力コンポーネント:**
```php
[
    'type' => 'text|email|password|number|date|textarea',
    'name' => string,
    'value' => mixed,
    'placeholder' => string,
    'required' => boolean,
    'disabled' => boolean,
    'readonly' => boolean,
    'validation' => array
]
```

**アラートコンポーネント:**
```php
[
    'type' => 'success|error|warning|info',
    'dismissible' => boolean,
    'icon' => boolean,
    'title' => string
]
```

## エラーハンドリング

### 統一エラー表示システム

**エラーメッセージコンポーネント:**
```php
// components/ui/alert.blade.php
@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => true,
    'title' => null
])

@php
$types = [
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800'
];

$icons = [
    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
];
@endphp

<div {{ $attributes->merge([
    'class' => 'border rounded-md p-4 ' . $types[$type],
    'role' => 'alert'
]) }} x-data="{ show: true }" x-show="show">
    <div class="flex">
        @if($icon)
            <div class="flex-shrink-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$type] }}"></path>
                </svg>
            </div>
        @endif
        
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-medium">{{ $title }}</h3>
                <div class="mt-2 text-sm">{{ $slot }}</div>
            @else
                <div class="text-sm">{{ $slot }}</div>
            @endif
        </div>
        
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button @click="show = false" class="inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>
```

### フォームバリデーション改善

**統一バリデーションエラー表示:**
```php
// components/forms/validation-error.blade.php
@props(['messages' => [], 'field' => null])

@if($messages && count($messages) > 0)
    <div class="mt-1" role="alert">
        @foreach($messages as $message)
            <p class="text-sm text-danger flex items-center gap-1">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $message }}
            </p>
        @endforeach
    </div>
@endif
```

## テスト戦略

### コンポーネントテスト

**Bladeコンポーネントテスト:**
```php
// tests/Feature/Components/ButtonComponentTest.php
class ButtonComponentTest extends TestCase
{
    /** @test */
    public function it_renders_primary_button_correctly()
    {
        $view = $this->blade('<x-ui.button variant="primary">Click me</x-ui.button>');
        
        $view->assertSee('Click me');
        $view->assertSeeInOrder(['bg-primary', 'hover:bg-primary-hover']);
    }
    
    /** @test */
    public function it_shows_loading_state()
    {
        $view = $this->blade('<x-ui.button :loading="true">Submit</x-ui.button>');
        
        $view->assertSee('animate-spin');
        $view->assertSeeInOrder(['disabled']);
    }
}
```

**アクセシビリティテスト:**
```php
// tests/Feature/AccessibilityTest.php
class AccessibilityTest extends TestCase
{
    /** @test */
    public function dropdown_has_proper_aria_attributes()
    {
        $view = $this->blade('<x-ui.dropdown>...</x-ui.dropdown>');
        
        $view->assertSeeInOrder(['role="button"', 'aria-haspopup="true"', 'role="menu"']);
    }
    
    /** @test */
    public function form_fields_have_proper_labels()
    {
        $view = $this->blade('<x-forms.form-group label="Email" name="email" required>...</x-forms.form-group>');
        
        $view->assertSeeInOrder(['<label for="email"', 'Email', '*']);
    }
}
```

### レスポンシブテスト

**ビューポートテスト:**
```javascript
// tests/js/responsive.test.js
describe('Responsive Design', () => {
    test('mobile navigation works correctly', async () => {
        await page.setViewport({ width: 375, height: 667 });
        await page.goto('/books');
        
        const mobileMenu = await page.$('[data-mobile-menu]');
        expect(mobileMenu).toBeTruthy();
    });
    
    test('tablet layout adjusts properly', async () => {
        await page.setViewport({ width: 768, height: 1024 });
        await page.goto('/books');
        
        const gridColumns = await page.$$eval('.book-grid > div', els => els.length);
        expect(gridColumns).toBeGreaterThan(1);
    });
});
```

この設計により、統一性があり、アクセシブルで、保守しやすいBladeテンプレートシステムを構築できます。
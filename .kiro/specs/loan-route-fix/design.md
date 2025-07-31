# 貸出ルート修正 - 技術設計書

## 概要

ナビゲーションテンプレートで参照されている `loans.index` ルートが実際には `admin.loans.index` として定義されているため、ルート参照の不整合を修正します。この問題は管理者ルートグループ内でルートが定義されているにも関わらず、ナビゲーションテンプレートで正しいプレフィックスが使用されていないことが原因です。

## アーキテクチャ

### 現在の問題

#### ルート定義（web.php）
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 貸出管理
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
});
```
実際のルート名: `admin.loans.index`

#### ナビゲーション参照（navigation.blade.php）
```php
<x-nav-link :href="route('loans.index')" :activeRoutes="'loans.index'">
    貸出履歴
</x-nav-link>
```
参照しているルート名: `loans.index` ← **これが間違い**

### 解決方法

ナビゲーションテンプレートの参照を正しいルート名 `admin.loans.index` に修正します。

## コンポーネントとインターフェース

### 修正対象ファイル

#### 1. resources/views/layouts/navigation.blade.php

**修正箇所1: デスクトップナビゲーション**
```php
// 修正前
<x-nav-link :href="route('loans.index')" :activeRoutes="'loans.index'">
    貸出履歴
</x-nav-link>

// 修正後
<x-nav-link :href="route('admin.loans.index')" :activeRoutes="'admin.loans.index'">
    貸出履歴
</x-nav-link>
```

**修正箇所2: モバイルナビゲーション**
```php
// 修正前
<a href="{{ route('loans.index') }}" @click="open = false" 
   class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-background hover:text-primary {{ request()->routeIs('loans.index') ? 'font-semibold text-primary' : '' }}">
   貸出履歴
</a>

// 修正後
<a href="{{ route('admin.loans.index') }}" @click="open = false" 
   class="block px-3 py-2 rounded-md text-base font-medium text-text-primary hover:bg-background hover:text-primary {{ request()->routeIs('admin.loans.index') ? 'font-semibold text-primary' : '' }}">
   貸出履歴
</a>
```

## データモデル

この修正はルート参照の修正のみのため、データモデルの変更は不要です。

## エラーハンドリング

### 修正前のエラー
```
Route [loans.index] not defined.
```

### 修正後の期待動作
- 管理者ユーザーが「貸出履歴」リンクをクリックした際に正常に `/admin/loans` ページに遷移
- アクティブルートのハイライトが正常に動作

## テスト戦略

### 単体テスト
- ルート定義の確認テスト
- ナビゲーションリンクの生成テスト

### 機能テスト
- 管理者ユーザーでのナビゲーション動作テスト
- アクティブルートハイライトの動作テスト
- 一般ユーザーには貸出履歴リンクが表示されないことの確認

### ブラウザテスト
- デスクトップナビゲーションでのリンククリック
- モバイルナビゲーションでのリンククリック
- 各ブラウザでの動作確認

## セキュリティ考慮事項

この修正はルート参照の修正のみのため、セキュリティ上の新たな考慮事項はありません。既存の管理者権限チェック（`admin` ミドルウェア）は引き続き有効です。

## パフォーマンス考慮事項

ルート参照の修正のみのため、パフォーマンスへの影響はありません。

## 実装手順

### ステップ1: ナビゲーションテンプレートの修正
1. `resources/views/layouts/navigation.blade.php` を開く
2. デスクトップナビゲーション部分の `route('loans.index')` を `route('admin.loans.index')` に修正
3. デスクトップナビゲーション部分の `'loans.index'` を `'admin.loans.index'` に修正
4. モバイルナビゲーション部分の `route('loans.index')` を `route('admin.loans.index')` に修正
5. モバイルナビゲーション部分の `request()->routeIs('loans.index')` を `request()->routeIs('admin.loans.index')` に修正

### ステップ2: 動作確認
1. 管理者ユーザーでログイン
2. ナビゲーションの「貸出履歴」リンクをクリック
3. エラーなく貸出管理ページに遷移することを確認
4. アクティブルートのハイライトが正常に動作することを確認

## 根本原因分析

この問題が発生した原因は、開発過程で以下の変更が行われた際に、ナビゲーションテンプレートの更新が漏れたことです：

1. **初期実装**: 貸出管理ルートが `loans.index` として定義
2. **管理者権限の実装**: セキュリティ強化のため、管理者専用機能を `admin` プレフィックス付きのルートグループに移動
3. **ナビゲーション更新漏れ**: ルート定義は更新されたが、ナビゲーションテンプレートの参照が更新されなかった

## 今後の予防策

1. **ルート変更時のチェックリスト作成**: ルート名を変更する際は、全てのビューファイルでの参照も同時に更新
2. **自動テストの強化**: ナビゲーションリンクの動作を確認するテストを追加
3. **コードレビューの強化**: ルート関連の変更時は、関連するビューファイルも確認対象に含める
<?php

// 貸出ルート修正の動作確認テスト
// このスクリプトは、ナビゲーションルートが正しく動作することを確認します

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Laravelアプリケーションを起動
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== 貸出ルート修正 動作確認テスト ===\n\n";

// 1. ルートの存在確認
echo "1. ルートの存在確認\n";
try {
    $adminLoansUrl = route('admin.loans.index');
    echo "✅ admin.loans.index ルートが存在します: {$adminLoansUrl}\n";
} catch (Exception $e) {
    echo "❌ admin.loans.index ルートが見つかりません: " . $e->getMessage() . "\n";
}

// 2. 古いルートが存在しないことを確認
echo "\n2. 古いルートの確認\n";
try {
    $oldLoansUrl = route('loans.index');
    echo "❌ 古い loans.index ルートがまだ存在します: {$oldLoansUrl}\n";
} catch (Exception $e) {
    echo "✅ 古い loans.index ルートは正しく削除されています\n";
}

// 3. ナビゲーションテンプレートの内容確認
echo "\n3. ナビゲーションテンプレートの確認\n";
$navigationPath = 'resources/views/layouts/navigation.blade.php';
if (file_exists($navigationPath)) {
    $navigationContent = file_get_contents($navigationPath);
    
    // admin.loans.index の使用確認
    if (strpos($navigationContent, "route('admin.loans.index')") !== false) {
        echo "✅ デスクトップナビゲーションで admin.loans.index を使用\n";
    } else {
        echo "❌ デスクトップナビゲーションで admin.loans.index が見つかりません\n";
    }
    
    if (strpos($navigationContent, "request()->routeIs('admin.loans.index')") !== false) {
        echo "✅ モバイルナビゲーションで admin.loans.index を使用\n";
    } else {
        echo "❌ モバイルナビゲーションで admin.loans.index が見つかりません\n";
    }
    
    // 古いルート参照がないことを確認
    if (strpos($navigationContent, "route('loans.index')") === false) {
        echo "✅ 古い loans.index ルート参照は削除されています\n";
    } else {
        echo "❌ 古い loans.index ルート参照がまだ残っています\n";
    }
    
} else {
    echo "❌ ナビゲーションテンプレートが見つかりません\n";
}

echo "\n=== テスト完了 ===\n";
echo "\n手動確認が必要な項目:\n";
echo "- ブラウザで http://localhost:8001 にアクセス\n";
echo "- admin@library.local / password でログイン\n";
echo "- 「貸出履歴」リンクをクリックしてエラーが発生しないことを確認\n";
echo "- アクティブルートハイライトが正常に動作することを確認\n";
echo "- モバイル表示でも同様に動作することを確認\n";
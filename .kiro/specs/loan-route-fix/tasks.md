# 貸出ルート修正 - 実装タスクリスト

## 実装タスク

- [x] 1. ナビゲーションテンプレートのデスクトップ版ルート参照を修正
  - resources/views/layouts/navigation.blade.php のデスクトップナビゲーション部分を修正
  - `route('loans.index')` を `route('admin.loans.index')` に変更
  - `:activeRoutes="'loans.index'"` を `:activeRoutes="'admin.loans.index'"` に変更
  - _要件: 1.1, 1.2, 1.3, 1.4_

- [x] 2. ナビゲーションテンプレートのモバイル版ルート参照を修正
  - resources/views/layouts/navigation.blade.php のモバイルナビゲーション部分を修正
  - `route('loans.index')` を `route('admin.loans.index')` に変更
  - `request()->routeIs('loans.index')` を `request()->routeIs('admin.loans.index')` に変更
  - _要件: 1.1, 1.2, 1.3, 1.4_

- [x] 3. 修正内容の動作確認テスト
  - 管理者ユーザーでログインして「貸出履歴」リンクの動作を確認
  - デスクトップとモバイル両方のナビゲーションでテスト
  - アクティブルートハイライトの動作確認
  - _要件: 1.1, 1.2, 1.4, 2.3, 2.4_

- [x] 4. 他のルート参照の整合性確認
  - 全てのBladeテンプレートで管理者ルートの参照が正しいことを確認
  - 他に同様の問題がないかチェック
  - _要件: 2.1, 2.2, 2.3_
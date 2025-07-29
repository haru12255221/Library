# 実装計画

- [x] 1. デザインシステムの基盤整備
  - 重複するレイアウトファイルを統一し、Tailwind設定を最適化する
  - 既存のapp-layout.blade.phpを削除し、app.blade.phpに統一する
  - カスタムCSS変数の使用を標準化する
  - _要件: 1.1, 1.2, 1.3_

- [x] 2. 統一ボタンコンポーネントの作成
  - [x] 2.1 基本ボタンコンポーネントの実装
    - components/ui/button.blade.phpを作成する
    - variant（primary, success, danger, secondary）とsize（sm, md, lg）のサポートを実装する
    - ローディング状態とdisabled状態の処理を追加する
    - _要件: 1.1, 7.1, 7.2_

  - [x] 2.2 既存ボタンコンポーネントの置き換え
    - primary-button.blade.phpとdanger-button.blade.phpを新しいボタンコンポーネントに置き換える
    - 全てのBladeファイルで新しいボタンコンポーネントを使用するよう更新する
    - _要件: 1.1, 7.1_

- [x] 3. フォームコンポーネントの改善
  - [x] 3.1 統一フォームグループコンポーネントの作成
    - components/forms/form-group.blade.phpを作成する
    - ラベル、必須マーク、エラーメッセージ、ヘルプテキストの統一表示を実装する
    - アクセシビリティ対応（適切なfor属性とaria-describedby）を追加する
    - _要件: 2.1, 2.2, 5.1_

  - [x] 3.2 入力コンポーネントの統一
    - text-input.blade.phpを拡張してtype属性とバリデーション状態をサポートする
    - input-error.blade.phpを改善してアクセシビリティを向上させる
    - フォーカス状態の統一（focus:ring-primaryの使用）を実装する
    - _要件: 1.1, 2.1, 5.1_

- [x] 4. アラートとメッセージコンポーネントの作成
  - [x] 4.1 統一アラートコンポーネントの実装
    - components/ui/alert.blade.phpを作成する
    - success, error, warning, infoの各タイプをサポートする
    - 閉じるボタン（dismissible）機能を実装する
    - _要件: 5.1, 5.2_

  - [x] 4.2 既存の成功・エラーメッセージの置き換え
    - books/index.blade.phpの成功メッセージを新しいアラートコンポーネントに置き換える
    - auth-session-status.blade.phpを新しいアラートコンポーネントベースに更新する
    - _要件: 5.1, 5.2_

- [x] 5. ナビゲーションコンポーネントの改善
  - [x] 5.1 ドロップダウンコンポーネントのアクセシビリティ向上
    - dropdown.blade.phpにキーボードナビゲーション（Enter, Space, Escape）を追加する
    - 適切なARIA属性（role, aria-haspopup, aria-expanded）を実装する
    - フォーカス管理を改善する
    - _要件: 2.1, 2.2_

  - [x] 5.2 パンくずナビゲーションコンポーネントの作成
    - components/navigation/breadcrumb.blade.phpを作成する
    - books/show.blade.phpの「書籍一覧に戻る」リンクをパンくずナビに置き換える
    - _要件: 2.1_

- [ ] 6. レスポンシブデザインの改善
  - [x] 6.1 カードコンポーネントの作成
    - components/ui/card.blade.phpを作成する
    - レスポンシブパディングとシャドウを実装する
    - books/index.blade.phpの書籍カードを新しいコンポーネントに置き換える
    - _要件: 3.1, 3.2, 7.1_

  - [x] 6.2 モバイル対応の改善
    - books/index.blade.phpの検索フォームをモバイルフレンドリーに改善する
    - ボタンサイズとタッチターゲットを44px以上に調整する
    - _要件: 3.1, 3.2_

- [x] 7. フォームインタラクションの改善
  - [x] 7.1 ローディング状態の実装
    - books/create.blade.phpのフォーム送信時にローディング状態を表示する
    - Alpine.jsを使用してボタンの無効化とローディングアニメーションを実装する
    - _要件: 4.1, 4.2_

  - [x] 7.2 フォームバリデーション表示の改善
    - components/forms/validation-error.blade.phpを作成する
    - エラーメッセージにアイコンとrole="alert"を追加する
    - books/create.blade.phpで新しいバリデーションコンポーネントを使用する
    - _要件: 5.1, 5.3_

- [ ] 8. コンポーネントライブラリの拡張
  - [x] 8.1 ローディングコンポーネントの作成
    - components/ui/loading.blade.phpを作成する
    - スピナーアニメーションとスケルトンローディングをサポートする
    - books/index.blade.phpの検索機能で使用する
    - _要件: 4.2, 7.1_

  - [x] 8.2 コンポーネントドキュメントの作成
    - 各コンポーネントの使用方法とプロパティを文書化する
    - resources/views/components/README.mdを作成する
    - _要件: 7.2, 7.3_

- [x] 9. 既存ページの統一コンポーネント適用
  - [x] 9.1 認証ページの更新
    - confirm-password.blade.php, forgot-password.blade.php, verify-email.blade.phpで新しいコンポーネントを使用する
    - フォームグループとボタンコンポーネントを適用する
    - _要件: 1.1, 2.1, 5.1_

  - [x] 9.2 プロフィールページの更新
    - profile/edit.blade.phpで新しいカードとフォームコンポーネントを使用する
    - 統一されたレイアウトとスタイリングを適用する
    - _要件: 1.1, 3.1, 7.1_

- [x] 10. テストとドキュメント
  - [x] 10.1 コンポーネントの動作確認
    - 各コンポーネントが正しく表示されることを確認する
    - レスポンシブデザインが適切に動作することをテストする
    - _要件: 3.1, 3.2_

  - [x] 10.2 アクセシビリティの検証
    - キーボードナビゲーションが正しく動作することを確認する
    - スクリーンリーダーでの読み上げをテストする
    - _要件: 2.1, 2.2, 2.3_
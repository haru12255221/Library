# vantanlib.com HTTPS化実装 タスクリスト

## 概要

このタスクリストは、vantanlib.comドメインでの本番環境HTTPS化を段階的に進めるための具体的な作業項目を定義します。Docker設定の統合、Let's Encrypt証明書の自動取得・更新、完全自動化された運用システムの構築を実現します。

## 実装タスク

- [x] 1.0 Docker設定ファイルの統合と整理
  - laravel-app/docker-compose.local.yml、docker-compose.production.ymlの削除
  - laravel-app/Dockerfile.localの削除
  - ルートディレクトリのdocker-compose.ymlを開発環境専用に統一
  - laravel-app/docker-compose.prod.ymlをvantanlib.com用本番環境設定に更新
  - 設定の重複を排除し、開発環境と本番環境の明確な分離を実現
  - _要件: 3.1, 3.2_

- [x] 1.1 開発環境用HTTPS設定の完了
  - 自己署名証明書の生成完了（docker/ssl/localhost.crt, localhost.key）
  - docker/nginx/default.dev.confでHTTPS設定完了（ポート8443）
  - 開発環境でのHTTPS接続動作確認済み
  - _要件: 1.1, 1.2_

- [x] 1.2 本番環境用Nginx設定ファイルの作成
  - docker/nginx/default.prod.confファイル作成済み
  - HTTP→HTTPSリダイレクト設定済み
  - Let's Encrypt認証用パス設定済み
  - セキュリティヘッダー設定済み
  - _要件: 4.1, 4.2, 4.3, 4.4_

- [x] 2.3 vantanlib.com用環境変数の更新
  - laravel-app/.env.productionをvantanlib.com用に完全更新
  - 本番環境用データベース設定の追加
  - セキュリティ設定の強化（セキュアクッキー、ドメイン設定）
  - 本番環境用メール設定の追加
  - Google Books API設定の確認
  - _要件: 3.4_

- [x] 1.4 証明書自動更新システムの基本実装
  - scripts/renew-ssl.shスクリプト作成済み
  - 基本的な更新ロジック実装済み
  - Nginx自動リロード機能実装済み
  - _要件: 2.3, 5.2, 5.3_

- [x] 2.1 vantanlib.com用Docker Compose設定の作成
  - docker-compose.prod.ymlをvantanlib.com用に完全書き換え
  - Certbotコンテナの追加（vantanlib.com + www.vantanlib.com対応）
  - 本番環境用Nginxコンテナ設定（ポート80/443）
  - 本番環境用アプリケーションコンテナ設定
  - 証明書用ボリュームマウント設定
  - _要件: 2.1, 5.1_

- [x] 2.2 vantanlib.com用Nginx設定の作成
  - docker/nginx/default.prod.confをvantanlib.com用に更新
  - www.vantanlib.com → vantanlib.comリダイレクト設定
  - HTTP/2対応とセキュリティヘッダー強化
  - カメラ機能対応のCSP設定追加
  - 静的ファイル最適化設定
  - _要件: 4.1, 4.2, 4.3, 4.4_

- [x] 2.3 HTTPS強制ミドルウェアの作成
  - laravel-app/app/Http/Middleware/ForceHttps.phpを作成
  - 本番環境でのHTTPS強制リダイレクト機能を実装
  - ミドルウェアの登録設定をbootstrap/app.phpに追加
  - _要件: 4.3_

- [x] 3.1 Mixed Content対策の実装
  - 既存コードでのHTTPリソース参照を調査
  - Google Books API呼び出しのHTTPS化を確認（現在はHTTPS使用済み）
  - 静的リソース（CSS、JS、画像）のHTTPS化を実装
  - _要件: 1.4_

- [ ] 3.2 HTTPS機能テストの実装
  - laravel-app/tests/Feature/HttpsTest.phpを作成
  - HTTPS自動リダイレクトのテストを実装
  - セキュリティヘッダーの検証テストを追加
  - SSL証明書の有効性確認テストを実装
  - _要件: 6.1, 6.4_

- [ ] 3.3 HTML5カメラ機能のHTTPS環境テスト
  - HTTPS環境でのカメラアクセス許可テストを実装
  - navigator.mediaDevices.getUserMedia動作確認
  - カメラ機能の自動テストスイートを作成
  - _要件: 1.3, 6.2_

- [ ] 3.4 Google Books API連携のHTTPS環境テスト
  - HTTPS環境でのAPI呼び出しテストを実装
  - Mixed Contentエラーの発生確認テスト
  - API連携の自動テストスイートを作成
  - _要件: 1.4, 6.3_

- [x] 4.1 vantanlib.com用証明書更新スクリプトの作成
  - scripts/renew-ssl.shをvantanlib.com用に完全書き換え
  - dry-run事前チェック機能の実装
  - 詳細ログ出力とメール通知機能の実装
  - 証明書有効期限確認機能の追加
  - エラーハンドリングと復旧処理の強化
  - _要件: 2.2, 2.3, 2.4, 5.2, 5.4_

- [ ] 4.2 自動更新Cron設定とテスト
  - 毎日午前2時実行のCronジョブ設定
  - ログローテーション設定（/var/log/ssl-renewal.log）
  - テスト環境での動作確認
  - 失敗時のアラート機能テスト
  - _要件: 5.2, 7.1, 7.4_

- [ ] 4.3 本番環境デプロイ前のテスト環境構築
  - ステージング環境用の設定を作成
  - Let's Encryptステージング環境での証明書取得テスト
  - DNS設定とファイアウォール設定の確認
  - _要件: 2.4_

- [ ] 5.1 vantanlib.com本番環境への初回デプロイ
  - vantanlib.comドメインのDNS設定確認（A/AAAAレコード）
  - 本番サーバーでのDocker環境構築
  - docker-compose.prod.ymlを使用した環境起動
  - Let's Encrypt証明書の初回取得実行
  - https://vantanlib.comでの動作確認
  - _要件: 5.1_

- [ ] 5.2 vantanlib.com監視システムの実装
  - SSL証明書有効期限監視スクリプトの作成
  - Nginxアクセス/エラーログの設定と監視
  - 証明書更新ログの監視とアラート設定
  - 管理者メール通知システムの実装
  - ヘルスチェック機能の実装
  - _要件: 7.1, 7.2, 7.3, 7.4_

- [x] 5.3 パフォーマンス最適化の実装
  - SSL セッションキャッシュの設定
  - HTTP/2プロトコルの有効化（既に設定済み）
  - 静的ファイル配信の最適化
  - OCSP Staplingの設定
  - _要件: パフォーマンス要件_

- [ ] 6.1 運用手順書の作成
  - 証明書更新手順の文書化
  - トラブルシューティングガイドの作成
  - 緊急時対応手順の文書化
  - _要件: 運用要件_

- [ ] 6.2 vantanlib.com最終動作確認とテスト
  - https://vantanlib.comでの全機能動作確認
  - HTML5カメラ機能のブラウザ許可テスト
  - Google Books API連携のMixed Content確認
  - セキュリティヘッダーとCSP設定の確認
  - パフォーマンステスト（HTTP/2、静的ファイル配信）
  - _要件: 6.1, 6.2, 6.3, 6.4_

## 注意事項

### vantanlib.com実装の前提条件
- vantanlib.comドメインが取得済みで、DNSがサーバーIPに向いていること
- 本番サーバーのポート80, 443が開放されていること
- admin@vantanlib.comメールアドレスが設定済みであること
- 開発環境でのHTTPS接続が正常に動作していること

### 重要な実装順序
1. **Docker設定統合**（タスク1.0）が最優先 - 設定の重複を排除
2. **vantanlib.com用設定作成**（タスク2.1-2.3） - ドメイン固有の設定
3. **DNS設定確認**後にLet's Encrypt証明書取得を実行
4. **証明書取得成功後**にHTTPS設定を有効化
5. **全機能テスト完了後**にvantanlib.com本番環境公開

### vantanlib.com固有のリスク管理
- Let's Encryptのレート制限（週5回まで）を避けるため、ステージング環境での事前テスト必須
- vantanlib.com証明書取得失敗時のロールバック手順を事前準備
- DNS設定変更時の伝播時間（最大48時間）を考慮したデプロイスケジュール
- www.vantanlib.com → vantanlib.comリダイレクトの動作確認
- カメラ機能のHTTPS環境での動作確認（ブラウザ許可が必要）
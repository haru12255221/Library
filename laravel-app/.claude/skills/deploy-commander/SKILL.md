---
name: deploy-commander
description: デプロイ・リリース手順の実行とチェックリスト確認
disable-model-invocation: true
argument-hint: "[環境: development|production]"
---

# デプロイ・リリース自動化スキル

$ARGUMENTS 環境へのデプロイをサポートします。

## 事前チェックリスト

### コード品質
- [ ] PHPエラーがないこと
- [ ] Bladeテンプレートにエラーがないこと
- [ ] dd()やdump()が残っていないこと

### Laravel固有
- [ ] マイグレーションが最新であること
- [ ] .envの設定が環境に合っていること
- [ ] APP_DEBUG=false（本番）
- [ ] APP_KEYが設定されていること

### 依存関係
- [ ] composer.lockが最新であること
- [ ] 不要なパッケージがないこと

## デプロイ手順

### ローカル開発
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Render（本番）
```
1. GitHubにpush → 自動デプロイ
2. Render管理画面で環境変数を確認
3. デプロイログでエラーがないことを確認
```

## トラブルシューティング

### デプロイエラー時
1. Renderのデプロイログを確認
2. 環境変数の設定を確認
3. DB接続（Neon）を確認

### よくある問題
- 500エラー → APP_KEYの設定漏れ、マイグレーション未実行
- DB接続エラー → Neonの接続文字列・SSLモード確認
- 静的ファイルが反映されない → `php artisan optimize:clear`

## 出力形式

```
## デプロイ状況

### チェック結果
- [x] 完了項目
- [ ] 未完了項目

### 実行コマンド
[実行したコマンドと結果]

### 注意事項
[あれば記載]
```

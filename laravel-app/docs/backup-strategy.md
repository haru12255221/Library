# データベースバックアップ戦略

## 本番環境（Neon PostgreSQL）

### 自動バックアップ（Neon提供）
- Neonは全プランで**ポイントインタイムリカバリ（PITR）**を提供
- 無料プランでは**過去7日間**の任意の時点に復元可能
- 設定不要（Neon側で自動的に実行）
- Neonダッシュボード > プロジェクト > Settings > Storage から確認

### 手動バックアップ（pg_dump）

#### 前提条件
- `pg_dump` コマンドが使える環境（ローカルPCまたはサーバー）
- Neonの接続情報（Renderの環境変数から取得）

#### 接続情報の確認
Renderダッシュボード > library-app > Environment から以下を確認：
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

#### バックアップ実行
```bash
pg_dump "postgresql://<DB_USERNAME>:<DB_PASSWORD>@<DB_HOST>/<DB_DATABASE>?sslmode=require" \
  --format=custom \
  --file=backup_$(date +%Y%m%d_%H%M%S).dump
```

#### リストア実行
```bash
pg_restore --clean --if-exists \
  -d "postgresql://<DB_USERNAME>:<DB_PASSWORD>@<DB_HOST>/<DB_DATABASE>?sslmode=require" \
  backup_XXXXXXXX_XXXXXX.dump
```

### 推奨運用
- **月1回**程度、手動で`pg_dump`を実行してローカルに保存
- Neonの自動バックアップで直近7日間はカバーされるため、手動バックアップは長期保存用
- バックアップファイルはGitにコミットしない（`.gitignore`に追加済み）

## ローカル開発環境（Docker MySQL）

- `mysql_data/` ディレクトリにデータが永続化されている
- 必要に応じて `mysqldump` でバックアップ可能
- 開発用データのため、特別なバックアップ運用は不要

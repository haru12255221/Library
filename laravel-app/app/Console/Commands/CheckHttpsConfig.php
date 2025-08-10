<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

/**
 * HTTPS設定確認コマンド
 * 
 * vantanlib.com用のHTTPS設定を確認するためのコマンドです。
 */
class CheckHttpsConfig extends Command
{
    /**
     * コマンドの名前とシグネチャ
     */
    protected $signature = 'https:check 
                            {--detailed : 詳細な設定情報を表示}';

    /**
     * コマンドの説明
     */
    protected $description = 'vantanlib.com用HTTPS設定の確認';

    /**
     * コマンドを実行
     */
    public function handle(): int
    {
        $this->info('🔒 vantanlib.com HTTPS設定確認');
        $this->newLine();

        // 基本設定の確認
        $this->checkBasicConfig();
        
        // ミドルウェア設定の確認
        $this->checkMiddlewareConfig();
        
        // 環境設定の確認
        $this->checkEnvironmentConfig();
        
        if ($this->option('detailed')) {
            $this->checkDetailedConfig();
        }

        $this->newLine();
        $this->info('✅ HTTPS設定確認が完了しました');

        return Command::SUCCESS;
    }

    /**
     * 基本設定の確認
     */
    private function checkBasicConfig(): void
    {
        $this->info('📋 基本設定');
        
        $appUrl = config('app.url');
        $appEnv = config('app.env');
        
        $this->line("  APP_URL: {$appUrl}");
        $this->line("  APP_ENV: {$appEnv}");
        
        // HTTPS URLかチェック
        if (str_starts_with($appUrl, 'https://')) {
            $this->info('  ✅ APP_URLがHTTPSに設定されています');
        } else {
            $this->warn('  ⚠️  APP_URLがHTTPに設定されています');
        }
        
        // vantanlib.comドメインかチェック
        if (str_contains($appUrl, 'vantanlib.com')) {
            $this->info('  ✅ vantanlib.comドメインが設定されています');
        } else {
            $this->warn('  ⚠️  vantanlib.com以外のドメインが設定されています');
        }
        
        $this->newLine();
    }

    /**
     * ミドルウェア設定の確認
     */
    private function checkMiddlewareConfig(): void
    {
        $this->info('🛡️  ミドルウェア設定');
        
        // ForceHttpsミドルウェアの存在確認
        $middlewareClass = \App\Http\Middleware\ForceHttps::class;
        
        if (class_exists($middlewareClass)) {
            $this->info('  ✅ ForceHttpsミドルウェアが存在します');
        } else {
            $this->error('  ❌ ForceHttpsミドルウェアが見つかりません');
        }
        
        // 本番環境でのミドルウェア有効化確認
        if (app()->environment('production')) {
            $this->info('  ✅ 本番環境でHTTPS強制が有効です');
        } else {
            $this->warn('  ⚠️  開発環境のためHTTPS強制は無効です');
        }
        
        $this->newLine();
    }

    /**
     * 環境設定の確認
     */
    private function checkEnvironmentConfig(): void
    {
        $this->info('🌐 環境設定');
        
        // セッション設定
        $sessionSecure = config('session.secure');
        $sessionDomain = config('session.domain');
        $sessionSameSite = config('session.same_site');
        
        $this->line("  セッションセキュア: " . ($sessionSecure ? 'true' : 'false'));
        $this->line("  セッションドメイン: {$sessionDomain}");
        $this->line("  セッションSameSite: {$sessionSameSite}");
        
        if ($sessionSecure) {
            $this->info('  ✅ セキュアクッキーが有効です');
        } else {
            $this->warn('  ⚠️  セキュアクッキーが無効です');
        }
        
        // Sanctum設定
        $sanctumDomains = config('sanctum.stateful');
        if ($sanctumDomains) {
            $this->line("  Sanctumドメイン: " . implode(', ', $sanctumDomains));
            
            if (in_array('vantanlib.com', $sanctumDomains)) {
                $this->info('  ✅ Sanctumでvantanlib.comが設定されています');
            }
        }
        
        $this->newLine();
    }

    /**
     * 詳細設定の確認
     */
    private function checkDetailedConfig(): void
    {
        $this->info('🔍 詳細設定');
        
        // データベース設定
        $dbConnection = config('database.default');
        $dbHost = config("database.connections.{$dbConnection}.host");
        
        $this->line("  データベース接続: {$dbConnection}");
        $this->line("  データベースホスト: {$dbHost}");
        
        // キャッシュ設定
        $cacheDriver = config('cache.default');
        $sessionDriver = config('session.driver');
        
        $this->line("  キャッシュドライバー: {$cacheDriver}");
        $this->line("  セッションドライバー: {$sessionDriver}");
        
        // メール設定
        $mailMailer = config('mail.default');
        $mailHost = config("mail.mailers.{$mailMailer}.host");
        
        $this->line("  メールドライバー: {$mailMailer}");
        $this->line("  メールホスト: {$mailHost}");
        
        // ログ設定
        $logChannel = config('logging.default');
        $logLevel = config('logging.channels.daily.level', 'debug');
        
        $this->line("  ログチャンネル: {$logChannel}");
        $this->line("  ログレベル: {$logLevel}");
        
        $this->newLine();
    }
}
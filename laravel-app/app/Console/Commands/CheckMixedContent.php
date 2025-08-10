<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\HttpsHelper;
use Illuminate\Support\Facades\File;

/**
 * Mixed Content検出コマンド
 * 
 * vantanlib.com用のMixed Content問題を検出・報告するコマンドです。
 */
class CheckMixedContent extends Command
{
    /**
     * コマンドの名前とシグネチャ
     */
    protected $signature = 'https:check-mixed-content 
                            {--scan-files : ファイルをスキャンしてMixed Contentを検出}
                            {--fix : 検出されたHTTP URLをHTTPSに自動修正}
                            {--report : 詳細レポートを生成}';

    /**
     * コマンドの説明
     */
    protected $description = 'vantanlib.com用Mixed Content問題の検出と修正';

    /**
     * コマンドを実行
     */
    public function handle(): int
    {
        $this->info('🔍 vantanlib.com Mixed Content検査を開始します');
        $this->newLine();

        // 基本設定チェック
        $this->checkBasicConfig();
        
        // Google Books API設定チェック
        $this->checkGoogleBooksApi();
        
        // Mixed Content保護機能チェック
        $this->checkMixedContentProtection();

        if ($this->option('scan-files')) {
            $this->scanFiles();
        }

        if ($this->option('report')) {
            $this->generateReport();
        }

        $this->newLine();
        $this->info('✅ Mixed Content検査が完了しました');

        return Command::SUCCESS;
    }

    /**
     * 基本設定のチェック
     */
    private function checkBasicConfig(): void
    {
        $this->info('📋 基本HTTPS設定チェック');
        
        $checks = HttpsHelper::checkVantanlibConfig();
        
        foreach ($checks as $key => $check) {
            $status = $check['status'] ? '✅' : '❌';
            $this->line("  {$status} {$check['name']}");
            $this->line("     現在値: {$check['value']}");
            
            if (!$check['status']) {
                $this->line("     期待値: {$check['expected']}");
            }
        }
        
        $this->newLine();
    }

    /**
     * Google Books API設定チェック
     */
    private function checkGoogleBooksApi(): void
    {
        $this->info('📚 Google Books API設定チェック');
        
        $checks = HttpsHelper::checkGoogleBooksApiConfig();
        
        foreach ($checks as $key => $check) {
            $status = $check['status'] ? '✅' : '❌';
            $this->line("  {$status} {$check['name']}");
            $this->line("     現在値: {$check['value']}");
        }
        
        $this->newLine();
    }

    /**
     * Mixed Content保護機能チェック
     */
    private function checkMixedContentProtection(): void
    {
        $this->info('🛡️ Mixed Content保護機能チェック');
        
        $checks = HttpsHelper::checkMixedContentProtection();
        
        foreach ($checks as $key => $check) {
            $status = $check['status'] ? '✅' : '❌';
            $this->line("  {$status} {$check['name']}");
            $this->line("     現在値: {$check['value']}");
        }
        
        $this->newLine();
    }

    /**
     * ファイルスキャン
     */
    private function scanFiles(): void
    {
        $this->info('🔍 ファイルスキャンを実行中...');
        
        $scanPaths = [
            'resources/views',
            'resources/js',
            'resources/css',
            'public',
        ];

        $totalIssues = 0;
        $scannedFiles = 0;

        foreach ($scanPaths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);
            
            foreach ($files as $file) {
                $scannedFiles++;
                $content = File::get($file->getPathname());
                $mixedContentUrls = HttpsHelper::detectMixedContent($content);
                
                if (!empty($mixedContentUrls)) {
                    $totalIssues += count($mixedContentUrls);
                    $this->warn("  ⚠️ {$file->getRelativePathname()}");
                    
                    foreach ($mixedContentUrls as $url) {
                        $this->line("     HTTP URL: {$url}");
                        
                        if ($this->option('fix')) {
                            $secureUrl = HttpsHelper::secureUrl($url);
                            $this->line("     修正後: {$secureUrl}");
                        }
                    }
                    
                    $this->newLine();
                }
            }
        }

        $this->info("📊 スキャン結果:");
        $this->line("  スキャンファイル数: {$scannedFiles}");
        $this->line("  検出された問題: {$totalIssues}");
        
        if ($totalIssues === 0) {
            $this->info("  🎉 Mixed Content問題は検出されませんでした！");
        }
        
        $this->newLine();
    }

    /**
     * 詳細レポート生成
     */
    private function generateReport(): void
    {
        $this->info('📄 詳細レポートを生成中...');
        
        $report = [
            'timestamp' => now()->toISOString(),
            'domain' => 'vantanlib.com',
            'environment' => app()->environment(),
            'basic_config' => HttpsHelper::checkVantanlibConfig(),
            'google_books_api' => HttpsHelper::checkGoogleBooksApiConfig(),
            'mixed_content_protection' => HttpsHelper::checkMixedContentProtection(),
        ];

        $reportPath = storage_path('logs/mixed-content-report-' . now()->format('Y-m-d-H-i-s') . '.json');
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("  📁 レポートが生成されました: {$reportPath}");
        $this->newLine();
    }
}
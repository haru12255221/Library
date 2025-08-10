<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\HttpsHelper;

/**
 * HTTPS関連サービスプロバイダー
 * 
 * vantanlib.com用のHTTPS機能とBladeディレクティブを提供します。
 */
class HttpsServiceProvider extends ServiceProvider
{
    /**
     * サービスの登録
     */
    public function register(): void
    {
        // HttpsHelperをシングルトンとして登録
        $this->app->singleton('https.helper', function () {
            return new HttpsHelper();
        });
    }

    /**
     * サービスの起動
     */
    public function boot(): void
    {
        // 本番環境でHTTPS強制
        if ($this->app->environment('production')) {
            $this->forceHttpsInProduction();
        }

        // Bladeディレクティブの登録
        $this->registerBladeDirectives();
    }

    /**
     * 本番環境でのHTTPS強制設定
     */
    private function forceHttpsInProduction(): void
    {
        // URL生成をHTTPS強制
        if (str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }

    /**
     * Bladeディレクティブの登録
     */
    private function registerBladeDirectives(): void
    {
        // @secureAsset ディレクティブ
        Blade::directive('secureAsset', function ($expression) {
            return "<?php echo \\App\\Helpers\\HttpsHelper::secureAsset({$expression}); ?>";
        });

        // @secureUrl ディレクティブ
        Blade::directive('secureUrl', function ($expression) {
            return "<?php echo \\App\\Helpers\\HttpsHelper::secureUrl({$expression}); ?>";
        });

        // @httpsOnly ディレクティブ（HTTPS環境でのみ表示）
        Blade::directive('httpsOnly', function () {
            return "<?php if (request()->secure()): ?>";
        });

        Blade::directive('endhttpsOnly', function () {
            return "<?php endif; ?>";
        });

        // @vantanlibDomain ディレクティブ（vantanlib.comドメインでのみ表示）
        Blade::directive('vantanlibDomain', function () {
            return "<?php if (str_contains(request()->getHost(), 'vantanlib.com')): ?>";
        });

        Blade::directive('endvantanlibDomain', function () {
            return "<?php endif; ?>";
        });

        // @mixedContentSafe ディレクティブ（Mixed Content安全な環境でのみ表示）
        Blade::directive('mixedContentSafe', function () {
            return "<?php if (\\App\\Helpers\\HttpsHelper::isHttpsEnforced()): ?>";
        });

        Blade::directive('endmixedContentSafe', function () {
            return "<?php endif; ?>";
        });
    }
}
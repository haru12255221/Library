<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * カラーパレットから色を取得
     */
    public static function get(string $key): string
    {
        return config("colors.{$key}", '#000000');
    }

    /**
     * インラインスタイル用の色を取得
     */
    public static function style(string $key): string
    {
        return 'color: ' . self::get($key) . ';';
    }

    /**
     * 背景色のインラインスタイルを取得
     */
    public static function bgStyle(string $key): string
    {
        return 'background-color: ' . self::get($key) . ';';
    }

    /**
     * ホバー効果付きのスタイルを取得
     */
    public static function hoverStyle(string $key, string $hoverKey): string
    {
        return sprintf(
            'background-color: %s; onmouseover="this.style.backgroundColor=\'%s\'" onmouseout="this.style.backgroundColor=\'%s\'"',
            self::get($key),
            self::get($hoverKey),
            self::get($key)
        );
    }
}
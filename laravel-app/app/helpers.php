<?php

use App\Helpers\ColorHelper;

if (!function_exists('color')) {
    /**
     * カラーパレットから色を取得
     */
    function color(string $key): string
    {
        return ColorHelper::get($key);
    }
}

if (!function_exists('colorStyle')) {
    /**
     * インラインスタイル用の色を取得
     */
    function colorStyle(string $key): string
    {
        return ColorHelper::style($key);
    }
}

if (!function_exists('bgColorStyle')) {
    /**
     * 背景色のインラインスタイルを取得
     */
    function bgColorStyle(string $key): string
    {
        return ColorHelper::bgStyle($key);
    }
}
<?php

if (!function_exists('format_count')) {
    /**
     * Twitter風の数値フォーマット（1万以上は "1.6万" 表記）
     */
    function format_count(int $n): string
    {
        if ($n >= 10000) {
            $man = round($n / 10000, 1);
            if ($man == floor($man)) {
                return (int) $man . '万';
            }
            return number_format($man, 1) . '万';
        }
        return number_format($n);
    }
}

<?php

/**
 * Polyfill for mbstring regex functions when mbstring PHP extension is blocked or missing.
 */

if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        $res = preg_split($delimiter . $escaped . $delimiter . 'u', $string, $limit);
        return $res === false ? false : $res;
    }
}

if (!function_exists('mb_regex_encoding')) {
    function mb_regex_encoding(?string $encoding = null): string|bool {
        return $encoding ? true : 'UTF-8';
    }
}

if (!function_exists('mb_ereg')) {
    function mb_ereg(string $pattern, string $string, ?array &$regs = null): bool {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        return (bool) preg_match($delimiter . $escaped . $delimiter . 'u', $string, $regs);
    }
}

if (!function_exists('mb_eregi')) {
    function mb_eregi(string $pattern, string $string, ?array &$regs = null): bool {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        return (bool) preg_match($delimiter . $escaped . $delimiter . 'iu', $string, $regs);
    }
}

if (!function_exists('mb_ereg_replace')) {
    function mb_ereg_replace(string $pattern, string $replacement, string $string, ?string $options = null): string|false {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        $modifier = 'u' . (str_contains($options ?? '', 'i') ? 'i' : '');
        return preg_replace($delimiter . $escaped . $delimiter . $modifier, $replacement, $string);
    }
}

if (!function_exists('mb_eregi_replace')) {
    function mb_eregi_replace(string $pattern, string $replacement, string $string, ?string $options = null): string|false {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        return preg_replace($delimiter . $escaped . $delimiter . 'iu', $replacement, $string);
    }
}

if (!function_exists('mb_ereg_match')) {
    function mb_ereg_match(string $pattern, string $string, ?string $options = null): bool {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        $modifier = 'u' . (str_contains($options ?? '', 'i') ? 'i' : '');
        return (bool) preg_match($delimiter . '^' . $escaped . $delimiter . $modifier, $string);
    }
}

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string {
        $len = mb_strlen($string, $encoding);
        if ($start >= $len) {
            return '';
        }
        $markerLen = mb_strlen($trim_marker, $encoding);
        if ($width <= $markerLen) {
            return mb_substr($trim_marker, 0, $width, $encoding);
        }
        $sub = mb_substr($string, $start, $width, $encoding);
        if (mb_strlen($sub, $encoding) + $start < $len) {
            $sub = mb_substr($string, $start, max(0, $width - $markerLen), $encoding) . $trim_marker;
        }
        return $sub;
    }
}


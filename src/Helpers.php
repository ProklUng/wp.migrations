<?php

namespace Arrilot\BitrixMigrations;

/**
 * Class Helpers
 * @package Arrilot\BitrixMigrations
 */
class Helpers
{
    /**
     * Convert a value to studly caps case.
     *
     * @param string $value Value.
     *
     * @return string
     */
    public static function studly(string $value) : string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Рекурсивный поиск миграций с поддирректориях.
     *
     * @param mixed   $pattern Pattern.
     * @param integer $flags   Does not support flag GLOB_BRACE
     *
     * @return array
     */
    public static function rGlob($pattern, $flags = 0) : array
    {
        if (!is_string($pattern)) {
            return [];
        }

        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, static::rGlob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}

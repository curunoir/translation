<?php

use curunoir\translation\Facades\TranslationStatic;

if (!function_exists('_t')) {
    /**
     * Shorthand function for translating text.
     *
     * @param string $text
     * @param array  $replacements
     * @param string $toLocale
     *
     * @return string
     */
    function _t($text, $toLocale = null, $parameters = null)
    {
        return TranslationStatic::translate($text, $toLocale, $parameters);
    }
}

if (!function_exists('d')) {
    /**
     * Shorthand function for translating text.
     *
     * @param string $text
     * @param array  $replacements
     * @param string $toLocale
     *
     * @return string
     */
    function d($var)
    {
        dump($var);
    }
}
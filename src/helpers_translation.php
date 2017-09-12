<?php
use Illuminate\Support\Facades\App;

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
    function _t($text, $toLocale = '')
    {
        return App::make('translationstatic')->translate($text, $toLocale);
    }
}
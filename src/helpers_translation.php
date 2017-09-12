<?php
use Illuminate\Support\Facades\App;
use \curunoir\translation\Models\Locale;

if (!function_exists('trad')) {
    /**
     * @param $slug
     * @param array $parameters
     * @param null $lang
     * @return mixed|null|string
     */
    function trad($slug, $parameters = array(), $lang = null)
    {
        if (isset($parameters['noTrad'])) return $slug;

        // Le singleton du package
        $translationLib = App::make('translationlib');

        if (Locale::$_lang)
            $lang = \curunoir\translation\Models\Locale::$_lang;

        if ($lang == null):
            $lang = session('code') ? session('code') : 'fr';
        endif;

        if ($lang == config('app.fallback_locale') || env('DESACTIV_TRAD') == 'TRUE'):
            return $slug;
        endif;

        $cache = $translationLib->getCacheTrad($lang);
        $cache = env('DEV') ? collect($cache) : $cache;
        // filters the Collection cache to find the translated $slug content
        $res = $cache->filter(function ($t) use ($slug) {
            return $t->translation == $slug;
        })->first();

        $line = $res ? $res->content : null;

        if ($line == ''):
            // If not found, the $slug will be translated
            $line = env('DEV') ? $translationLib->addTrad($slug, $lang) : $slug;
        endif;

        if (!empty($parameters)):
            foreach ($parameters as $key => $value):
                $line = str_replace(':' . $key, $value, $line);
            endforeach;
        endif;
        return $line;
    }
}
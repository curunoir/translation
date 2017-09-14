<?php

use curunoir\translation\Facades\TranslationStatic;
use curunoir\translation\Facades\TranslationDyn;
use Illuminate\Database\Eloquent\Model;

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

function testDyn($text)
{
    return TranslationDyn::translate("salut");
}

function tradDyn($content, $model, $field, $object_id, $locale_id = null)
{
    $data['content'] = $content;
    $data['model'] = $model;
    $data['field'] = $field;
    $data['object_id'] = $object_id;
    return TranslationDyn::getOne($data);
}

/**
 * @param $data
 * $data = [
            'champ1' => [                 // field du tableau $fillTrad
                '1' => 'Un beau site',  // locale_id => traduction,
                '2' => 'A beautiful site'
            ],
            'champ2' => [
                '1' => 'La description du beau site',
                '2' => 'Beautiful site description'
            ]
    ];
 * @param Model $model
 */
function addTradDyn($data, Model $model)
{
    $model->saveTrad($data);
}

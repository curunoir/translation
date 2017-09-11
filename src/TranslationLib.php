<?php

namespace curunoir\translation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use curunoir\translation\Models\Translation;
use curunoir\translation\Models\Locale;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslationLib
{
    private $_instance = [];

    public function getCacheTrad($lang) {

        if(!isset($this->_instance[$lang])) {
            $this->_instance[$lang] = $this->cacheTrad($lang);
        }

        return $this->_instance[$lang];
    }

    public function addTrad($text, $lang)
    {
        $locale = DB::table('locales')
            ->where('code', $lang)
            ->first();
        if (!$locale) return $text;
        $source = Translation::where('translation', $text)
            ->where('locale_id', 1)
            ->first();

        if ($source):

            $trad = $source->child()
                ->where('locale_id', $locale->id)
                ->first();
            if ($trad):
                return $trad->translation;
            else:

                $gT = new TranslateClient();
                $gT->setSource('fr');
                $gT->setTarget($lang);
                dd($text);
                try {
                    $textTrad = $gT->translate(mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES'));
                    $newT = new Translation();

                    $newT->locale_id = $locale->id;
                    $newT->translation_id = $source->id;
                    $newT->translation = $textTrad;
                    $newT->save();
                    Log::info('Google Translation: ' . $text . ' => ' . $textTrad);
                    return $textTrad;

                } catch (\ErrorException $e) {
                    Log::error('Error Google Translation: ' . $text);
                    return $text;
                    // Request to translate failed, set the text
                    // to the parent translation.

                } catch (\UnexpectedValueException $e) {
                    Log::error('Error Google Translation: ' . $text);
                    return $text;
                    // Looks like something other than text was passed in,
                    // we'll set the text to the parent translation
                    // for this exception as well.
                }
            endif;
        else:
            $source = new Translation();
            $source->locale_id = 1;
            $source->translation = $text;
            $source->save();

            if ($lang != 'fr'):
                $gT = new TranslateClient();
                $gT->setSource('fr');
                $gT->setTarget($lang);
                try {
                    $textTrad = $gT->translate(mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES'));
                    $newT = new Translation();
                    $newT->locale_id = $locale->id;
                    $newT->translation_id = $source->id;
                    $newT->translation = $textTrad;
                    $newT->save();
                    Log::info('Google Translation: ' . $text . ' => ' . $textTrad);
                    return $textTrad;

                } catch (\ErrorException $e) {
                    Log::error('Error Google Translation: ' . $text);
                    return $text;
                    // Request to translate failed, set the text
                    // to the parent translation.

                } catch (\UnexpectedValueException $e) {
                    Log::error('Error Google Translation: ' . $text);
                    return $text;
                    // Looks like something other than text was passed in,
                    // we'll set the text to the parent translation
                    // for this exception as well.
                }
            endif;

        endif;
    }

    public function cacheTrad($lang)
    {
        if (env('DEV')):
            return \Illuminate\Support\Facades\Cache::remember('translations_dev_' . $lang,20, function () use ($lang) {
                $tmp = \Illuminate\Support\Facades\DB::table('translations');
                if ($lang) {

                    $locale_id = Locale::where('code', $lang)->first()->id;
                } else {
                    $locale_id = 1;
                }

                $tmp->rightjoin('translations as tlang', 'translations.id', '=', 'tlang.translation_id')
                    ->select('translations.translation as translation', 'tlang.translation as content')
                    ->where('tlang.locale_id', $locale_id);

                return $tmp->get();
            });

        else://production

            return \Illuminate\Support\Facades\Cache::remember('translations_' . $lang,20, function () use ($lang){
                $locale = Locale::where('code',$lang)->first();
                $l = collect(Translation::getFileNoCrypt('translations'));

                $localeSource = Locale::where('code','fr')->first();
                $source = $l->filter(function($item) use($locale,$localeSource){
                    return $item->locale_id==$localeSource->id;
                });

                $target = $l->filter(function($item) use($locale){
                    return $item->locale_id==$locale->id;
                });

                $translations = $target->map(function($item) use ($source){
                    $s = $source->filter(function($el) use($item){
                        return $el->id == $item->translation_id;
                    })->first();
                    $item->content = $item->translation;
                    $item->translation = $s->translation;


                    return $item;
                });

                return $translations;

            });
        endif;
    }
}

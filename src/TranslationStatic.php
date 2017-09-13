<?php

namespace curunoir\translation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Foundation\Application;
use curunoir\translation\Models\TranslationStatic As TransStaticModel;
use curunoir\translation\Contracts\Translation as TranslationInterface;
use curunoir\translation\Models\Locale;
use InvalidArgumentException;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslationStatic implements TranslationInterface
{

    /*
     * Array of Collections of objects
     * Each Collection represents the cache for a lang
     * The objects of the Collections are database translations with 'content' and 'translation' fields
     */
    private     $_instance = [];

    protected   $locale = '';
    protected   $localeModel;
    protected   $translationModel;
    protected   $config;
    protected   $request;
    private     $cacheTime = 20;

    /**
     * TranslationStatic constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $_instance = [];

        $this->config  = $app->make('config');
        $this->request = $app->make('request');

        // Default configuration from application file
        $this->localeModel      = $app->make($this->getConfigLocaleModel());
        $this->translationModel = $app->make($this->getConfigTranslationModel());

        $this->setLocale($this->getConfigDefaultLocale()); // From the config file, can be changed by cookie within the middleware
        $this->setCacheTime($this->getConfigCacheTime());
    }

    /**
     * @param string $text
     * @param string $lang
     * @return mixed|null|string
     */
    public function translate($text, $lang = NULL)
    {
        // Make sure $text is actually a string and not and object / int
        $this->validateText($text);

        if (!is_null($lang)) {
            $localeModel = $this->firstOrCreateLocale($lang);
        } else {
            $lang = $this->getLocale();
            $localeModel = $this->firstOrCreateLocale($lang);
        }

        if($lang == $this->getConfigDefaultLocale())
            return $text;

        // Search the cache of the lang
        $cache = $this->getCacheTrad($lang);

        // filters the Collection cache to find the translated $text content
        $res = $cache->filter(function ($t) use ($text) {

            return $t->translation == $text;
        })->first();

        $line = $res ? $res->content : null;

        // If not found, the $slug will be translated
        if ($line == '')
            $line = $this->addTrad($text, $lang);

        return $line;
    }

    /**
     * Retrieves the cache of a lang
     * @param $lang string lang of the cache we're searching for
     * @return Collection
     */
    public function getCacheTrad($lang)
    {
        if(!isset($this->_instance[$lang]))
            $this->_instance[$lang] = $this->cacheTrad($lang);
        return $this->_instance[$lang];
    }

    /**
     * Retrieves the cache of a lang or creates it from the database
     * @param $lang The lang of the cache we're searching for
     * @return Collection
     */
    public function cacheTrad($lang)
    {
        return \Illuminate\Support\Facades\Cache::remember('translations' . $lang, $this->cacheTime, function () use ($lang) {

            $tmp = \Illuminate\Support\Facades\DB::table('translations');
            if ($lang) {
                $found_locale = Locale::where('code', $lang)->first();
                if ($found_locale)
                    $locale_id = Locale::where('code', $lang)->first()->id;
                else
                    $locale_id = 1;
            } else {
                $locale_id = 1;
            }

            $tmp->rightjoin('translations as tlang', 'translations.id', '=', 'tlang.translation_id')
                ->select('translations.translation as translation', 'tlang.translation as content')
                ->where('tlang.locale_id', $locale_id);

            return collect($tmp->get());
        });
    }

    /**
     * Add a translation on database and cache entry and returns it
     * @param $text
     * @param $lang
     * @return mixed|string
     */
    public function addTrad($text, $lang)
    {
        $locale = DB::table('locales')
            ->where('code', $lang)
            ->first();
        if (!$locale) return $text;
        $source = TransStaticModel::where('translation', $text)
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

                try {
                    $textTrad = $gT->translate(mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES'));
                    $newT = new TransStaticModel();

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
            $source = new TransStaticModel();
            $source->locale_id = 1;
            $source->translation = $text;
            $source->save();

            if ($lang != 'fr'):
                $gT = new TranslateClient();
                $gT->setSource('fr');
                $gT->setTarget($lang);
                try {
                    $textTrad = $gT->translate(mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES'));
                    $newT = new TransStaticModel();
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


    //////////////////////////////////////////////// From Steve Bauman package

    /**
     * Retrieves or creates a locale from the specified code.
     *
     * @param string $code
     *
     * @return Model
     */
    protected function firstOrCreateLocale($code)
    {
        $name = $this->getConfigLocaleByCode($code);
        $locale = $this->localeModel->firstOrCreate([
            'code' => $code,
            'name' => $name,
            'activ' => 1
        ]);
        return $locale;
    }

    /**
     * Returns the locale model from the configuration.
     *
     * @return string
     */
    protected function getConfigLocaleModel()
    {
        return $this->config->get('translation.models.locale', Models\Locale::class);
    }

    /**
     * Returns the translation model from the configuration.
     *
     * @return string
     */
    protected function getConfigTranslationModel()
    {
        return $this->config->get('translation.models.translationStatic', Models\TranslationStatic::class);
    }

    /**
     * Validates the inserted text to make sure it's a string.
     *
     * @param $text
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    protected function validateText($text)
    {
        if (!is_string($text)) {
            $message = 'Invalid Argument. You must supply a string to be translated.';

            throw new InvalidArgumentException($message);
        }

        return true;
    }


    public function getLocale()
    {
        if ($this->request->hasCookie('locale')) {
            return $this->request->cookie('locale');
        } else {
            return $this->getConfigDefaultLocale();
        }
    }

    public function setLocale($code = '')
    {
        $this->locale = $code;
    }

    public function getRoutePrefix()
    {
        $locale = $this->request->segment($this->getConfigRequestSegment());

        $locales = $this->getConfigLocales();

        if (is_array($locales) && in_array($locale, array_keys($locales))) {
            return $locale;
        }
    }

    /**
     * Returns the array of configuration locales.
     *
     * @return array
     */
    protected function getConfigLocales()
    {
        return $this->config->get('translation.locales');
    }

    /**
     * Returns a the english name of the locale code entered from the config file.
     *
     * @param string $code
     *
     * @return string
     */
    protected function getConfigLocaleByCode($code)
    {
        $locales = $this->getConfigLocales();

        if (is_array($locales) && array_key_exists($code, $locales)) {
            return $locales[$code];
        }

        return $code;
    }

    /**
     * Returns the request segment to retrieve the locale from.
     *
     * @return int
     */
    protected function getConfigRequestSegment()
    {
        return $this->config->get('translation.request_segment', 1);
    }

    /**
     * Sets the time to store the translations and locales in cache.
     *
     * @param int $time
     */
    protected function setCacheTime($time)
    {
        if (is_numeric($time)) {
            $this->cacheTime = $time;
        }
    }

    /**
     * Returns the default locale from the configuration.
     *
     * @return string
     */
    protected function getConfigDefaultLocale()
    {
        return $this->config->get('translation.default_locale', 'fr');
    }

    /**
     * Returns the cache time set from the configuration file.
     *
     * @return string|int
     */
    protected function getConfigCacheTime()
    {
        return $this->config->get('translation.cache_time', $this->cacheTime);
    }

    public function getAppLocale()
    {
        return $this->config->get('app.locale');
    }


}

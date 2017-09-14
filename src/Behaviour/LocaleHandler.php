<?php

namespace curunoir\translation\Behaviour;

use curunoir\translation\Models\Locale;
/**
 * Trait LocaleHandler
 * Must be used by translations services which have to deal with locales selection from Url, config, cookie, session etc
 * @package curunoir\translation\Behaviour
 */
trait LocaleHandler
{

    protected   $locale = '';
    protected   $config;
    protected   $request;
    private     $cacheTime = 20;

    public function getLocale()
    {
        if ($this->locale == '')
            $this->locale = $this->getConfigDefaultLocale();
        return $this->locale;
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
    public function getConfigLocales()
    {
        return $this->config->get('translation.locales');
    }

    /**
     * Returns the locale model from the configuration.
     *
     * @return string
     */
    public function getConfigLocaleModel()
    {
        return $this->config->get('translation.models.locale', Locale::class);
    }

    /**
     * Returns the array of configuration allowed locales.
     *
     * @return array
     */
    public function getConfigAllowedLocales()
    {
        return $this->config->get('translation.allowed_locales');
    }

    /**
     * Returns a the english name of the locale code entered from the config file.
     *
     * @param string $code
     *
     * @return string
     */
    public function getConfigLocaleByCode($code)
    {
        $locales = $this->getConfigLocales();

        if (is_array($locales) && array_key_exists($code, $locales)) {
            return $locales[$code];
        }

        return $code;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getLocaleIdByCode($code)
    {
        $locale = $this->localeModel->where([
            'code' => $code,
            'activ' => 1
        ])->first();
        if(!$locale)
            return null;
        else
            return $locale->id;
    }

    /**
     * Returns the default locale from the configuration.
     *
     * @return string
     */
    public function getConfigDefaultLocale()
    {
        return $this->config->get('translation.default_locale', 'fr');
    }

    /**
     * Returns the default locale id from the configuration and database
     *
     * @return string
     */
    public function getConfigDefaultLocaleId()
    {
        return $this->getLocaleIdByCode($this->getConfigDefaultLocale());
    }

    /**
     * Retrieves or creates a locale from the specified code.
     *
     * @param string $code
     *
     * @return Model
     */
    public function firstOrCreateLocale($code)
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
     * Returns the request segment to retrieve the locale from.
     *
     * @return int
     */
    public function getConfigRequestSegment()
    {
        return $this->config->get('translation.request_segment', 1);
    }

    public function getAppLocale()
    {
        return $this->config->get('app.locale');
    }

}
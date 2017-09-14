<?php
namespace curunoir\translation;

use Illuminate\Contracts\Foundation\Application;
use curunoir\translation\Models\TranslationDyn As TransDynModel;
use curunoir\translation\Contracts\Translation as TranslationInterface;
use curunoir\translation\Behaviour\LocaleHandler;
use curunoir\translation\Behaviour\CacheHandler;
use curunoir\translation\Models\Locale;

class TranslationDyn implements TranslationInterface
{
    use LocaleHandler;
    use CacheHandler;

    /** Remember this class should be invoked as a singleton
     * so we don't need static member to handle the cache
     */
    public $_instance = [];
    protected   $localeModel;
    protected   $translationModel;

    public function __construct(Application $app)
    {
        $this->_instance = [];

        // config, locale, request are defined in LocaleHandler trait
        $this->config  = $app->make('config');
        $this->request = $app->make('request');

        // Default configuration from application file
        $this->localeModel      = $app->make($this->getConfigLocaleModel());
        $this->translationModel = $app->make($this->getConfigTranslationModel());

        $this->setLocale($this->getConfigDefaultLocale()); // From the config file, can be changed by cookie within the middleware
        $this->setCacheTime($this->getConfigCacheTime());
    }


    public function translate($text, $lang = NULL, $parameters = null)
    {

    }

    /**
     * Add a new translation in database
     * @param $data Array Must contain 'content' 'model' 'object_id' values
     */
    public function addTrad($data)
    {
        if (!isset($data['content']) || !isset($data['model']) || !isset($data['object_id'])):
            return dd('Error Field Translation Dyn');
        endif;
        $content = $data['content'];
        unset($data['content']);
        if ($data['locale_id'] > 1):
            $source = TransDynModel::where('locale_id', 1)
                ->where('model', $data['model'])
                ->where('object_id', $data['object_id'])
                ->where('field', $data['field'])
                ->first();
            if (!$source) dd('FR non présent');
            $data['translationsdyn_id'] = $source->id;
        endif;
        $trans = TransDynModel::firstOrCreate($data);
        $trans->content = $content;
        $trans->save();
    }

    /**
     * @param $data
     * @param bool $localTrad
     * @return mixed|null
     */
    public function getOne($data, $localTrad = false)
    {
        if (!isset($this->_instance['transDyn'])):
            $this->_instance['transDyn'] = TransDynModel::get();
        endif;
        $tmp = $this->_instance['transDyn'];
        $trans = null;

        if ($localTrad):
            $transLocal = TransDynModel::where('locale_id', $data['locale_id'])
                ->where('model', $data['model'])
                ->where('field', $data['field'])
                ->where('object_id', $data['object_id'])
                ->whereNotNull('content')
                ->first();

            if ($transLocal)
                return $transLocal->content;
        endif;
        $trans = $tmp->filter(function ($item) use ($data) {
            if ($item->locale_id == $data['locale_id'] &&
                $item->model == $data['model'] &&
                $item->field == $data['field'] &&
                $item->object_id == $data['object_id'] &&
                $item->content != ''
            )
                return $item;
        })->first();

        if ($trans)
            return $trans->content;

        return null;
    }

    /**
     * @param $data
     * @param bool $localTrad
     * @return array
     */
    public function getAll($data, $localTrad = false)
    {
        $tmp = [];
        foreach (Locale::getAll() as $l):
            $data['locale_id'] = $l->id;
            $tmp[$l->id] = $this->getOne($data, $localTrad);
        endforeach;
        return $tmp;
    }



    /**
     * Returns the translation model from the configuration.
     *
     * @return string
     */
    protected function getConfigTranslationModel()
    {
        return $this->config->get('translation.models.translationDyn', Models\TranslationDyn::class);
    }

}
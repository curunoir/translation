<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 14/09/2017
 * Time: 08:48
 */

namespace curunoir\translation\Behaviour;

use curunoir\translation\Models\TranslationDyn;
use curunoir\translation\Facades\TranslationDyn as TransDynFacade;

/**
 * Trait TranslatableModel
 * Handles dynamic translation for models fields
 * @package curunoir\translation\Behaviour
 */
trait TranslatableModel
{

    protected static function boot() {
        parent::boot();

        static::deleting(function($model) {
            $model->delTrad();
        });
    }


    /**
     * @param $params
     */
    public function addTrad($params)
    {
        $params['locale_id'] = isset($params['locale_id']) ? $params['locale_id'] : 1;
        $params['model'] = explode('\\', get_class())[2];
        $params['object_id'] = $this->id;
        TransDynFacade::addTrad($params);
    }

    /**
     * @param $key
     * @return string
     */
    public function getAttributeValue($key)
    {
        if (!isset($this->fillTrad))
            dd('fillTrad no exist Model');

        if(in_array($key, $this->fillTrad))
            return $this->getTranslatedAttribute(['field' => $key], parent::getAttributeValue($key));
        else
            return parent::getAttributeValue($key);
    }

    /**
     *
     * @param $params => 'field' mandatory, => 'locale_id' optional
     * @return null
     */
    public function getTrad($params)
    {
        $params['locale_id'] = isset($params['locale_id']) ? $params['locale_id'] : TransDynFacade::getConfigDefaultLocaleId();
        $params['model'] = explode('\\', get_class())[2];

        if ($this->id)
            $params['object_id'] = $this->id;
        else
            return null;

        $content = TransDynFacade::getOne($params, $this->localTrad);

        if ($content)
            return $content;

        $model = get_class();

        $noTrad = $model::find($params['object_id']);

        if ($noTrad && $noTrad->{$params['field']})
            return $noTrad->{$params['field']};
    }


    /**
     * Get all the translations for a model
     * @return array
     */
    public function getAllTrad()
    {
        if (!isset($this->fillTrad))
            dd('fillTrad no exist Model');
        $params['model'] = explode('\\', get_class())[2];
        $trans = [];
        foreach ($this->fillTrad as $key):
            $params['object_id'] = $this->id;
            $params['field'] = $key;
            $trans[$key] = TransDynFacade::getAll($params, $this->localTrad);
        endforeach;
        return $trans;
    }

    /**
     * Add all the translations from the result of getAllTrad
     * @param $data
     */
    public function addAllTrad($data)
    {
        foreach ($this->fillTrad as $field):
            foreach ($data[$field] as $locale_id => $content):
                if ($content) {
                    $tmp = [
                        'field' => $field,
                        'model' => explode('\\', get_class())[2],
                        'locale_id' => $locale_id,
                        'object_id' => $this->id,
                        'content' => $content,
                        'translationsdyn_id' => isset($transDynID) ? $transDynID : null,
                    ];
                    $new = TranslationDyn::create($tmp);
                    $transDynID = $locale_id == '1' ? $new->id : null;
                }
            endforeach;
        endforeach;
    }

    /**
     * Structure attendue de $data :
     * [
          'title' => [                 // field du tableau $fillTrad
               '1' => 'Un beau site',  // locale_id => traduction,
               '2' => 'A beautiful site'
              ],
           'description' => [
              '1' => 'La description du beau site',
               '2' => 'Beautiful site description'
               ]
      ]
     * @param $data
     */
    public function saveTrad(&$data)
    {
        if (!isset($this->fillTrad))
            dd('fillTrad no exist Model');

        foreach ($this->fillTrad as $key):
            if (isset($data[$key])):
                foreach ($data[$key] as $locale_id => $content):
                    $params['locale_id'] = $locale_id;
                    $params['content'] = $content;
                    $params['field'] = $key;
                    $this->addTrad($params);
                    unset($data[$key]);
                endforeach;
            endif;
        endforeach;
    }

    /**
     * Deletes all dynamic translations for a model line
     */
    public function delTrad(){
        $t = TranslationDyn::where('model',explode('\\', get_class())[2])
            ->where('object_id',$this->id)
            ->delete();
    }

    /**
     * @param $params
     */
    public function getTranslatedAttribute($params, $data)
    {
        $res = $this->getTrad($params);
        return $res ? $res : $data;
    }
}
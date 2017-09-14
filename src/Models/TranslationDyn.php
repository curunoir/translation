<?php

namespace curunoir\translation\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationDyn extends Model
{
    public $table = "translationsdyn";
    private static $_instance = [];

    public $fillable = [
        'locale_id',
        'translationsdyn_id',
        'content',
        'model',
        'object_id',
        'field'
    ];

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    /**
     * The original word or sentence to be translated
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(self::class, 'translationsdyn_id', 'id');
    }

    /**
     * @param $data
     */
    public static function add($data)
    {
        if (!isset($data['content']) || !isset($data['model']) || !isset($data['object_id'])):
            return dd('Error Field Translation Dyn');
        endif;
        $content = $data['content'];
        unset($data['content']);
        if ($data['locale_id'] > 1):
            $source = self::where('locale_id', 1)
                ->where('model', $data['model'])
                ->where('object_id', $data['object_id'])
                ->where('field', $data['field'])
                ->first();
            if (!$source) dd('FR non présent');
            $data['translationsdyn_id'] = $source->id;
        endif;
        $trans = self::firstOrCreate($data);
        $trans->content = $content;
        $trans->save();
    }

    /**
     * @param $data
     * @param bool $localTrad
     * @return mixed|null
     */
    public static function getOne($data, $localTrad = false)
    {
        if (!isset($_instance['transDyn'])):
            $_instance['transDyn'] = self::get();
        endif;
        $tmp = $_instance['transDyn'];
        $trans = null;

        if ($localTrad):
            $transLocal = self::where('locale_id', $data['locale_id'])
                ->where('model', $data['model'])
                ->where('field', $data['field'])
                ->where('object_id', $data['object_id'])
                ->whereNotNull('content')
                ->first();

            if ($transLocal) return $transLocal->content;
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
    public static function getAll($data, $localTrad = false)
    {
        $tmp = [];
        foreach (Locale::getAll() as $l):
            $data['locale_id'] = $l->id;
            $tmp[$l->id] = TranslationDyn::getOne($data, $localTrad);
        endforeach;
        return $tmp;
    }
}
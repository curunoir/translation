<?php

namespace App\Models;

use App\Behaviour\CommonClass;
use App\Behaviour\CommonModel;
use Stevebauman\Translation\Models\Translation as STrans;

class Translation extends STrans
{

    use CommonClass;
    protected $fillable = [
        'locale_id',
        'translation_id',
        'translation',
        'slug',
        'context'
    ];

    public function child()
    {
        return $this->hasMany(self::class);
    }

    public function scopeLast($query)
    {
        $query->orderBy('id', 'DESC');
    }

    public function setTranslationAttribute($trans)
    {
        $this->attributes['translation'] = ucfirst($trans);
        $this->attributes['slug'] = str_slug($trans,'_');
    }


}

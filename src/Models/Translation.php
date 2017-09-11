<?php

namespace curunoir\translation\Models;

use Stevebauman\Translation\Models\Translation as STrans;

class Translation extends STrans
{
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

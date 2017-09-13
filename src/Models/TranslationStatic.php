<?php

namespace curunoir\translation\Models;
use Illuminate\Database\Eloquent\Model;

class TranslationStatic extends Model
{
    protected $fillable = [
        'locale_id',
        'translation_id',
        'translation',
        'slug',
        'context'
    ];

    /**
     * The locale translations table.
     *
     * @var string
     */
    protected $table = 'translations';

    protected $primaryKey  = "id";

    public function child()
    {
        return $this->hasMany(self::class, 'translation_id');
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


    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }


    public function parent()
    {
        return $this->belongsTo(self::class, 'translation_id');
    }


}

<?php

namespace curunoir\translation\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{

    /**
     * The fillable locale attributes.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'lang_code',
        'name',
        'display_name',
    ];

    private static $_instance = [];
    public static $_lang = null;

    /**
     * {@inheritdoc].
     */
    public function translations()
    {
        return $this->hasMany(Translation::class);
    }

    public function getIsoDateAttribute(){
        return str_replace('-','_',$this->iso).'.utf-8';
    }

    public static function getAll(){
        if(!isset(self::$_instance['all'])){
            self::$_instance['all'] = self::get();
        }
        return self::$_instance['all'];
    }

    public static function getWithCode($code=null){
        $code = $code ? $code : session('code');
        if(!isset(self::$_instance[$code])){
            self::$_instance[$code] = self::where('code',$code)->first();
        }

        return self::$_instance[$code];
    }

    public static function current(){
        if(!isset(self::$_instance['current'])){
            self::$_instance['current'] = self::where('code',session('code'))->first();
        }
        return self::$_instance['current'];
    }
}

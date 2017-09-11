<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stevebauman\Translation\Models\Locale as SLocale;

class Locale extends SLocale
{
    private static $_instance = [];
    public static $_lang = null;


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

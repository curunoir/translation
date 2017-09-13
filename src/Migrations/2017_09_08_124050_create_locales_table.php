<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('code')->unique();
            $table->string('lang_code')->nullable();
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->boolean('activ')->default(1);
            $table->string('iso')->nullable();
        });

        // Default locales
        DB::table('locales')->insert(
            array(
                'code' => 'fr',
                'lang_code' => NULL,
                'name' => 'French',
                'display_name' => 'Français',
                'activ' => 1,
                'iso' => 'fr-FR'
            )
        );

        DB::table('locales')->insert(
            array(
                'code' => 'en',
                'lang_code' => NULL,
                'name' => 'English',
                'display_name' => 'English',
                'activ' => 1,
                'iso' => 'en-GB'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locales');
    }
}

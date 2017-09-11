<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationsdynTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translationsdyn', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('locale_id')->index()->unsigned();
            $table->integer('translationsdyn_id')->index()->unsigned()->nullable();
            $table->longText('content');
            $table->string('model');
            $table->integer('object_id');
            $table->string('field');
            $table->timestamps();
        });
        Schema::table('translationsdyn', function (Blueprint $table) {
            $table->foreign('locale_id')->references('id')->on('locales')
                ->onDelete('cascade');
            $table->foreign('translationsdyn_id')->references('id')->on('translationsdyn')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translationsdyn');
    }
}

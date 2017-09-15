<?php

Route::post('ajax/forcetrad','curunoir\translation\Http\Controllers\Ajax\TranslaterController@forceTrad');
Route::post('quickupdate', 'curunoir\translation\Http\Controllers\Ajax\TranslaterController@postQuickUpdate');

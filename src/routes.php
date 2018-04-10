<?php
Route::group(
    ['prefix' => 'backend', 'as' => 'backend.', 'namespace' => 'Datlv\Enum', 'middleware' => config('enum.middleware')],
    function () {
        Route::group(
            ['prefix' => 'enum', 'as' => 'enum.'],
            function () {
                Route::post('order', ['as' => 'order', 'uses' => 'Controller@order']);
                Route::get('of/{type}', ['as' => 'type', 'uses' => 'Controller@index']);
                Route::post('{enum}/quick_update', ['as' => 'quick_update', 'uses' => 'Controller@quickUpdate']);
            });
        Route::resource('enum', 'Controller', ['except' => 'show']);
    }
);

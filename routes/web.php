<?php

Route::group(['middleware' => ['web']], function () {

    // Auth Routes
    Auth::routes();

    // Landing Page Route
    Route::get('/', 'HomeController@index');

    // INFRA Sale Routes
    Route::group(['prefix' => 'infra'], function() {
        Route::get('/', 'InfraController@index')->name('infra.index');
        Route::get('{infra_id}', 'InfraController@show')->name('infra.show');
        Route::post('/', 'InfraController@uploadStore')->name('infra.uploadstore');
        Route::post('{infra_id}', 'InfraController@process')->name('infra.process');
        Route::get('/sale/test', 'InfraController@testSale')->name('infra.testsale');
    });

    // Manual Sale Routes
    Route::group(['prefix' => 'manual'], function() {
        Route::get('/', 'ManualController@index')->name('manual.index');
        Route::post('/', 'ManualController@process')->name('manual.process');
        Route::get('create', 'ManualController@create')->name('manual.create');
        Route::post('store', 'ManualController@store')->name('manual.store');

        Route::group(['prefix' => 'preview'], function() {
            Route::get('bw', function() {
                return View::make('saletags.previewbw');
            })->name('preview.bw');
            Route::get('color', function() {
                return View::make('saletags.previewcolor');
            })->name('preview.color');
            Route::get('posquery/{upc}', 'ManualController@POSQuery')->name('manual.posquery');
        });
    });

    // Special AJAX Routes
    Route::group(['prefix' => 'ajax'], function() {
        Route::get('queueCountInfra', 'AjaxController@queueCountInfra')->name('ajax.queuecount.infra');
        Route::get('queueCountsManual', 'AjaxController@queueCountsManual')->name('ajax.queuecounts.manual');
        Route::get('jobCounts', 'AjaxController@jobCounts')->name('ajax.jobcounts');
    });
});

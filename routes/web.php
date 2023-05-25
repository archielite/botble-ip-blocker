<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'ArchiElite\IpBlocker\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'settings/ip-blocker'], function () {
            Route::match(['GET', 'POST'], '', [
                'as' => 'ip-blocker.settings',
                'uses' => 'IpBlockerController@settings',
            ]);

            Route::post('/update', [
                'as' => 'ip-blocker.settings.update',
                'uses' => 'IpBlockerController@updateSettings',
                'permission' => 'ip-blocker.settings',
            ]);
        });
    });
});
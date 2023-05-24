<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'ArchiElite\IpBlocker\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'settings/ip-blocker'], function () {
            Route::get('', [
                'as' => 'ip-blocker.loadIpBlocker',
                'uses' => 'IpBlockerController@loadIpBlocker',
            ]);

            Route::post('', [
                'as' => 'ip-blocker.updateIpBlocker',
                'uses' => 'IpBlockerController@updateIpBlocker',
                'permission' => 'ip-blocker.updateIpBlocker',
            ]);
        });
    });
});
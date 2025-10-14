<?php

// @formatter:off

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('test/{method?}/{arg1?}/{arg2?}/{arg3?}/{arg4?}', 'TestController@route');


/***************************************/
/*              Front
/***************************************/

Route::group(['prefix' => '/', 'as' => 'front', 'namespace' => 'Front'], function () {
    Route::get('/', ['as' => '.login', 'uses' => 'HomeController@login']);
    Route::get('/forgot-password', ['as' => '.forgot-password', 'uses' => 'HomeController@forgotPassword']);
    Route::post('/forgot-password', ['as' => '.post-forgot-password', 'uses' => 'HomeController@postForgotPassword']);
    Route::get('/invite/{code}', ['as' => '.inviteCode', 'uses' => 'HomeController@invite']);
    Route::post('/createAccount', ['as' => '.createAccount', 'uses' => 'HomeController@createAccount']);

    Route::group(['prefix' => '', 'as' => 'widget', 'namespace' => 'Widget'], function () {
        Route::get('widget/{code}', ['as' => '.index', 'uses' => 'WidgetController@widget']);
        Route::get('fixedWidget/{code}', ['as' => '.index', 'uses' => 'WidgetController@fixedWidget']);
        Route::post('widgetAjaxRequest/{code}', ['as' => '.ajax-request', 'uses' => 'WidgetController@ajaxRequest']);
        Route::post('changeCurrency/{code}', ['as' => '.ajax-currency-request', 'uses' => 'WidgetController@ajaxChangeCurrency']);
        Route::get('test', ['as' => '.test', 'uses' => 'WidgetController@test']);
    });
});

/***************************************/
/*              Customer
/***************************************/

Route::group(['prefix' => 'customer', 'as' => 'customer', 'namespace' => 'Customer'], function () {
    Route::get('dashboard', ['as' => '.dashboard', 'uses' => 'DashboardController@dashboard']);
    Route::get('logOut', ['as' => '.logOut', 'uses' => 'DashboardController@logOut']);


    Route::group(['prefix' => 'widget', 'as' => '.widget', 'namespace' => 'Widget'], function () {
        Route::get('/', ['as' => '.index', 'uses' => 'WidgetController@index']);
        Route::get('create', ['as' => '.create', 'uses' => 'WidgetController@create']);
        Route::post('store', ['as' => '.store', 'uses' => 'WidgetController@store']);
        Route::get('edit', ['as' => '.edit', 'uses' => 'WidgetController@edit']);
        Route::post('update', ['as' => '.update', 'uses' => 'WidgetController@update']);
        Route::delete('delete', ['as' => '.delete', 'uses' => 'WidgetController@delete']);
    });

    Route::group(['prefix' => 'rate', 'as' => '.rate', 'namespace' => 'Rate'], function () {
        Route::get('/', ['as' => '.index', 'uses' => 'RateController@index']);
        Route::post('/ajax-request', ['as' => '.ajax-request', 'uses' => 'RateController@rateRequest']);
        Route::post('store', ['as' => '.store', 'uses' => 'RateController@store']);
    });

    Route::group(['prefix' => 'statistics', 'as' => '.statistics', 'namespace' => 'Statistic'], function () {
        Route::get('/', ['as' => '.index', 'uses' => 'StatisticController@index']);
    });

    Route::group(['prefix' => 'hotels', 'as' => '.hotels', 'namespace' => 'Hotel'], function () {
        Route::get('/', ['as' => '.index', 'uses' => 'HotelController@index']);
        Route::get('create', ['as' => '.create', 'uses' => 'HotelController@create']);
        Route::post('store', ['as' => '.store', 'uses' => 'HotelController@store']);
        Route::get('edit', ['as' => '.edit', 'uses' => 'HotelController@edit']);
        Route::post('update', ['as' => '.update', 'uses' => 'HotelController@update']);
        Route::delete('delete', ['as' => '.delete', 'uses' => 'HotelController@delete']);
    });
});

/***************************************/
/*              Admin
/***************************************/

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['admin']], function () {
    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index']);


    Route::group(['prefix' => 'settings', 'as' => 'settings.', 'namespace' => 'Settings'], function () {
        Route::get('/', ['as' => 'index', 'uses' => 'SettingsController@index']);
        Route::get('/clearCache', ['as' => 'clear-cache', 'uses' => 'SettingsController@clearCache']);
        Route::put('/setCachingTime', ['as' => 'caching-time', 'uses' => 'SettingsController@setCachingTime']);
        Route::get('/setStatus', ['as' => 'set-status', 'uses' => 'SettingsController@setWidgetsStatus']);
    });

    Route::group(['prefix' => 'users', 'as' => 'users.', 'namespace' => 'User'], function () {
        Route::get('/', ['as' => 'index', 'uses' => 'UserController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'UserController@create']);
        Route::post('store', ['as' => 'store', 'uses' => 'UserController@store']);
        Route::get('edit/{id}', ['as' => 'edit', 'uses' => 'UserController@edit']);
        Route::put('update/{id}', ['as' => 'update', 'uses' => 'UserController@update']);
        Route::get('delete/{id}', ['as' => 'delete', 'uses' => 'UserController@destroy']);
        Route::get('invite', ['as' => 'invite', 'uses' => 'UserController@invite']);
        Route::get('deleteInvitation/{id}', ['as' => 'deleteInvitation', 'uses' => 'UserController@deleteInvitation']);
        Route::post('postInvite', ['as' => 'postInvite', 'uses' => 'UserController@postInvite']);
        Route::get('switchUser/{id}', ['as' => 'switchUser', 'uses' => 'UserController@switchUser']);
    });
});

Route::group(['prefix' => 'reseller', 'as' => 'reseller.', 'namespace' => 'Reseller', 'middleware' => ['reseller']], function () {

    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index']);

    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('/', ['as' => 'index', 'uses' => 'UserController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'UserController@create']);
        Route::post('store', ['as' => 'store', 'uses' => 'UserController@store']);
        Route::get('edit/{id}', ['as' => 'edit', 'uses' => 'UserController@edit']);
        Route::put('update/{id}', ['as' => 'update', 'uses' => 'UserController@update']);
        Route::get('delete/{id}', ['as' => 'delete', 'uses' => 'UserController@destroy']);
        Route::get('invite', ['as' => 'invite', 'uses' => 'UserController@invite']);
        Route::get('deleteInvitation/{id}', ['as' => 'deleteInvitation', 'uses' => 'UserController@deleteInvitation']);
        Route::post('postInvite', ['as' => 'postInvite', 'uses' => 'UserController@postInvite']);
        Route::get('switchUser/{id}', ['as' => 'switchUser', 'uses' => 'UserController@switchUser']);
        Route::get('exportExcel', ['as' => 'exportExcel', 'uses' => 'UserController@exportExcel']);
    });
});

/***************************************/
/*              API
/***************************************/
Route::group(['prefix' => 'api', 'as' => 'api.', 'namespace' => 'Api'], function () {
    Route::post('/', ['as' => 'index', 'uses' => 'ApiController@getPrice']);
    Route::get('/{widgetCode}', ['as' => 'index', 'uses' => 'ApiController@getRequest']);
});

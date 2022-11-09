<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

Route::group(['middleware' => 'auth:web'], function () {
    Route::get('/', 'IndexController@index');

    Route::get('/home', 'HomeController@index')->name('home');
    Route::post('/dashboard/async/getDateRanges', 'HomeController@asyncGetDateRanges');
    Route::post('/dashboard/async/getTiles', 'HomeController@asyncGetTiles');
    Route::post('/dashboard/async/getCharts', 'HomeController@asyncGetCharts');
    Route::post('/dashboard/async/getLists', 'HomeController@asyncGetLists');
    Route::post('/dashboard/export', 'HomeController@export');

    Route::get('/review', 'ReviewController@index');
    Route::post('/review/export', 'ReviewController@export');
    Route::post('/review/change', 'ReviewController@change');
    Route::get('/review/reply/create/{reviewId}', 'ReviewController@create')->where('reviewId', '[0-9]+');
    Route::post('/review/reply/store', 'ReviewController@store');
    Route::get('/review/reply/edit/{reviewId}', 'ReviewController@edit')->where('reviewId', '[0-9]+');
    Route::post('/review/reply/update', 'ReviewController@update');
    Route::post('/review/reply/delete', 'ReviewController@delete');
    Route::post('/review/async/findIsAutoreplied', 'ReviewController@asyncFindIsAutoreplied');
    Route::post('/review/async/findLocations', 'ReviewController@asyncFindLocations');
    Route::post('/review/async/updateAutoReplied', 'ReviewController@asyncUpdateAutoReplied');
    Route::post('/review/async/getAutoReplied', 'ReviewController@asyncGetAutoReplied');
    
    Route::get('/template', 'ReviewReplyTemplateController@index');
    Route::get('/template/create', 'ReviewReplyTemplateController@create');
    Route::post('/template/store', 'ReviewReplyTemplateController@store');
    Route::get('/template/{reviewReplyTemplateId}', 'ReviewReplyTemplateController@edit')->where('reviewReplyTemplateId', '[0-9]+');
    Route::post('/template/update', 'ReviewReplyTemplateController@update');
    Route::post('/template/delete', 'ReviewReplyTemplateController@delete');
    Route::post('/template/async/find', 'ReviewReplyTemplateController@asyncFind');

    Route::get('/localpost', 'LocalPostGroupController@index');
    Route::get('/localpost/create', 'LocalPostGroupController@create');
    Route::post('/localpost/store', 'LocalPostGroupController@store');
    Route::get('/localpost/edit/{localPostGroupId}', 'LocalPostGroupController@edit');
    Route::post('/localpost/update', 'LocalPostGroupController@update');
    Route::post('/localpost/export', 'LocalPostGroupController@export');
    Route::post('/localpost/upload', 'LocalPostGroupController@upload');
    Route::delete('/localpost/destroy/{localPostGroupId}', 'LocalPostGroupController@destroy');

    Route::get('/location', 'LocationController@index');
    Route::get('/location/create', 'LocationController@create');
    Route::post('/location/store', 'LocationController@store');
    Route::get('/location/edit/{locationId}', 'LocationController@edit');
    Route::post('/location/update', 'LocationController@update');
    Route::delete('/location/destroy/{locationId}', 'LocationController@destroy');

    Route::get('/photo', 'PhotoController@index');
    Route::get('/photo/create', 'PhotoController@create');
    Route::get('/photo/edit/{mediaItem2GroupId}', 'PhotoController@edit');
    Route::post('/photo/store', 'PhotoController@store');
    Route::post('/photo/update', 'PhotoController@update');
    Route::post('/photo/delete', 'PhotoController@delete');

    Route::get('/contact', 'ContactController@index');

    Route::get('/photo/create', 'PhotoController@create');

    // ユーザー管理
    Route::get('/user/register', 'UserController@showRegistrationForm')->name('user.register');
    Route::resource('user', 'UserController')->except(['create' ]);
});

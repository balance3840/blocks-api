<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('roles')->group(function () {
    Route::get('/', 'RoleController@index');
    Route::post('/', 'RoleController@create');
    Route::get('/{id}', 'RoleController@show');
    Route::put('/{id}', 'RoleController@update');
});

Route::prefix('statuses')->group(function () {
    Route::get('/', 'StatusController@index');
    Route::post('/', 'StatusController@create');
    Route::get('/{id}', 'StatusController@show');
    Route::put('/{id}', 'StatusController@update');
});

Route::prefix('stages')->group(function () {
    Route::get('/', 'StageController@index');
    Route::post('/', 'StageController@create');
    Route::get('/{id}', 'StageController@show');
    Route::put('/{id}', 'StageController@update');
});

Route::prefix('institutes')->group(function () {
    Route::get('/', 'InstituteController@index');
    Route::post('/', 'InstituteController@create');
    Route::get('/{id}', 'InstituteController@show');
    Route::put('/{id}', 'InstituteController@update');
});

Route::prefix('groups')->group(function () {
    Route::get('/', 'GroupController@index');
    Route::post('/', 'GroupController@create');
    Route::get('/{id}', 'GroupController@show');
    Route::put('/{id}', 'GroupController@update');
});

<?php

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

Route::view('/', 'index')->name('index');

// Registration: SRP Enrollment
Route::get('register', '\App\User\Controllers\Register@show')->name('user.register');
Route::post('register', '\App\User\Controllers\Register@store')->name('user.register');

// Authentication: SRP Challenge
Route::get('login', '\App\User\Controllers\Login@show')->name('user.login');
Route::post('login', '\App\User\Controllers\Login@store')->name('user.login');
Route::patch('login', '\App\User\Controllers\Login@update')->name('user.login');

// Deauthentication: Destroy Session
Route::any('logout', '\App\User\Controllers\Login@delete')->name('user.logout');

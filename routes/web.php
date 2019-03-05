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
Route::get('register', '\App\User\Register\Controller@show')->name('user.register');
Route::post('register', '\App\User\Register\Controller@store')->name('user.register');

// Authentication: SRP Challenge
Route::get('login', '\App\User\Auth\Controller@show')->name('user.login');
Route::post('login', '\App\User\Auth\Controller@store')->name('user.login');
Route::patch('login', '\App\User\Auth\Controller@update')->name('user.login');

// Deauthentication: Destroy Session
Route::any('logout', '\App\User\Auth\Controller@delete')->name('user.logout');

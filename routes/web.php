<?php
use Illuminate\Support\Facades\Artisan;
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

Route::get('/userbar', 'HomeController@userbar');

Route::get('auth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('auth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');

Route::middleware(['auth'])->group(function() {
    Route::get('/', 'HomeController@base');
    Route::get('/home', 'HomeController@index')->middleware(['age_consent', 'completed_registration', 'timezoned'])->name('home');
    Route::post('/consent', 'HomeController@consent_store')->name('consent_store');
    Route::get('/consent', 'HomeController@consent')->name('consent');
});

Route::middleware(['auth', 'timezoned'])->group(function () {
    Route::get('/post-login/new', 'Auth\PostLoginController@create')->name('post_login_post');
    Route::post('/post-login', 'Auth\PostLoginController@store')->name('post_login_store');
});

Route::middleware(['auth', 'completed_registration', 'timezoned'])->group(function () {
    Route::get('/games/{game}/edit', 'GamesController@edit')->name('game_edit');
    Route::get('/games/{game}/approve', 'GamesController@approve')->name('game_approve');
    Route::post('/games/{game}/approve', 'GamesController@approveStore')->name('game_approve');
    Route::get('/games/new', 'GamesController@create')->name('game_post');
    Route::post('/games', 'GamesController@store')->name('game_store');
    Route::get('/games/success', 'GamesController@success')->name('game_success');
    Route::get('/games/sorteo', 'GamesController@sorteo')->name('game_sorteo');
    Route::get('/games/{game}/fav', 'GamesController@fav')->name('game_add_fav');
    Route::get('/games/{game}/unfav', 'GamesController@unfav')->name('game_remove_fav');
    Route::get('/games/{game1}/{game2}/exchange', 'GamesController@exchange')->name('game_exchange_fav');

    Route::get('/talks/{game}/edit', 'TalksController@edit')->name('talk_edit');
    Route::get('/talks/{game}/approve', 'TalksController@approve')->name('talk_approve');
    Route::post('/talks', 'TalksController@store')->name('talk_store');
    Route::get('/talks/new', 'TalksController@create')->name('talk_post');
    Route::get('/talks/success', 'TalksController@success')->name('talk_success');

    Route::get('/multiple_sessions/{game}', 'MultipleSessionsController@create')->name('multiple_sessions_post');
    Route::post('/multiple_sessions/{game}', 'MultipleSessionsController@store')->name('multiple_sessions_store');
});

Route::middleware(['timezoned'])->group(function () {
    Route::put('/games', 'GamesController@index')->name('game_filter');
    Route::get('/games', 'GamesController@index')->name('game_list');
    Route::get('/games/{game}', 'GamesController@show')->name('game_view');
    Route::put('/talks', 'TalksController@index')->name('talk_filter');
    Route::get('/talks', 'TalksController@index')->name('talk_list');
    Route::get('/talks/{game}', 'TalksController@show')->name('talk_view');
});

Route::get('/games/{game}/register', 'GamesController@register')->name('game_register');
Route::get('/games/{game}/register_waitlist', 'GamesController@registerToWaitlist')->name('game_register_waitlist');
Route::get('/games/{game}/unregister', 'GamesController@unregister')->name('game_unregister');
Route::get('/games/{game}/unregister/{user}', 'GamesController@unregisterPlayer')->name('game_unregister_user');
Route::get('/games/{game}/unregister_waitlist', 'GamesController@unregisterToWaitlist')->name('game_unregister_waitlist');
Route::post('/games/{game}/message', 'MessagesController@store')->name('message_create');
Route::get('/storage/{filename}', 'GamesController@showImage')->name('storage_get');

/*Route::get('/clear-cache', function() {
   $exitCode = Artisan::call('cache:clear');
   // return what you want
   return 1;
});*/

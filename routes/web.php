<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\PostController@index');
Route::get('/home', ['as' => 'home', 'uses' => 'App\Http\Controllers\PostController@index']);

Route::get('/logout', 'App\Http\Controllers\UserController@logout');
Route::group(['prefix' => 'auth'], function () {
    Auth::routes();
});

// Vérification Login utilisateur
Route::middleware(['auth'])->group(function () {
    // Nouveau post
    Route::get('new-post', 'App\Http\Controllers\PostController@create');
    // Save nouveau post
    Route::post('new-post', 'App\Http\Controllers\PostController@store');
    // Editer post
    Route::get('edit/{slug}', 'App\Http\Controllers\PostController@edit');
    // enregistrer modif post
    Route::post('update', 'App\Http\Controllers\PostController@update');
    // supprimer post
    Route::get('delete/{id}', 'App\Http\Controllers\PostController@destroy');
    // Afficher posts
    Route::get('my-all-posts', 'App\Http\Controllers\UserController@user_posts_all');
    // Afficher drafts utilisateur
    Route::get('my-drafts', 'App\Http\Controllers\UserController@user_posts_draft');
    // Ajouter un commentaire
    Route::post('comment/add', 'App\Http\Controllers\CommentController@store');
    // Supprimer un commentaire
    Route::post('comment/delete/{id}', 'App\Http\Controllers\CommentController@distroy');
});

//Profil utilisateur
Route::get('user/{id}', 'App\Http\Controllers\UserController@profile')->where('id', '[0-9]+');
// Afficher liste de posts
Route::get('user/{id}/posts', 'App\Http\Controllers\UserController@user_posts')->where('id', '[0-9]+');
// Afficher un seul post
Route::get('/{slug}', ['as' => 'post', 'uses' => 'App\Http\Controllers\PostController@show'])->where('slug', '[A-Za-z0-9-_]+');

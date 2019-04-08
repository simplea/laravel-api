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

Route::get('/info/vul-detail/token/{token}', function ($token) {
    return redirect('/vul_Bulletin?token='.$token);
});
Route::get('/info/detail/id/{id}', function ($id) {
    return redirect('/detail?id='.$id);
});

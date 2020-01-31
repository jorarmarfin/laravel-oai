<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('retriv-dspace','HomeController@retrivdrupal');
Route::get('cosecha-dspace/{cantidad}','HomeController@cosecha');
<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('sembrar-dspace','HomeController@sembrando');
Route::get('cosecha-dspace/{cantidad}','HomeController@cosecha');
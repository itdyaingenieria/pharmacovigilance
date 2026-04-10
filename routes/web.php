<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pharmacovigilance/login', function () {
    return view('pharmacovigilance');
});

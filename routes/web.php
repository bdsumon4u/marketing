<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/binary-tree', function () {
    return view('binary-tree');
})->name('binary-tree');

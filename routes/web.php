<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::redirect('/', Filament::getDefaultPanel()->getPath());

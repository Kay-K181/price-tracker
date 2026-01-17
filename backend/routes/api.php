<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScrapeController;

Route::post('/scrape', [ScrapeController::class, 'scrape']);

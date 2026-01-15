<?php

use Illuminate\Support\Facades\Route;
use \App\Services\ScraperService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'database' => 'connected',
        'timestamp' => now()
    ]);
});

Route::get('/api/scrape-test', function (ScraperService $scraper) {
    $result = $scraper->scrapeProduct('https://www.myprotein.com/p/sports-nutrition/impact-whey-isolate-powder/10530911/?variation=10889149');
    return response()->json($result);
});

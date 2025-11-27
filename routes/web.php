<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for Vue.js SPA (must be last to not interfere with API routes)
Route::namespace('App\Http\Controllers')->group(function () {
    Route::get('{any?}', 'IndexController@index')->where('any', '^(?!api).*$');
});

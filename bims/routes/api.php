<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| This file is kept minimal. Broadcasting auth is handled automatically by
| Laravel when the Broadcast::routes() call is present in bootstrap/app.php
| (via the `channels` routing key). No additional API routes are needed
| for the current BIMS feature set.
*/

Route::get('/health', fn() => response()->json(['status' => 'ok']));

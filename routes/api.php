<?php

use App\Http\Controllers\Integration\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Integration API (B5 / B7): /api/integration/v1/...
|--------------------------------------------------------------------------
| /health needs no key. Everything else goes inside the integration.key group.
*/

Route::prefix('integration/v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::middleware('integration.key')->group(function () {
        // Endpoints come in Hakbang 8: /products, /warehouses, /stock, /stock-movements
    });
});

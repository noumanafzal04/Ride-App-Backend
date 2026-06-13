<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /**
     * auth route
     */
    require __DIR__ . '/api/auth.php';
    require __DIR__ . '/api/driver.php';
});

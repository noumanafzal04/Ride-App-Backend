<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppModule;
use App\Support\ApiResponse;

class ModuleController extends Controller
{
    /** Module flags the app reads to show/hide features. */
    public function index()
    {
        $modules = AppModule::orderBy('sort')->get(['key', 'name', 'icon', 'enabled', 'sort']);

        return ApiResponse::success(['modules' => $modules], 'Modules.');
    }
}

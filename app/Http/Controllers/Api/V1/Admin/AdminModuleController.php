<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppModule;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminModuleController extends Controller
{
    public function index()
    {
        return ApiResponse::success(
            ['modules' => AppModule::orderBy('sort')->get()],
            'Modules.'
        );
    }

    public function update(Request $request, string $key)
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        $module = AppModule::where('key', $key)->firstOrFail();
        $module->update(['enabled' => $data['enabled']]);

        return ApiResponse::success(['module' => $module], 'Module updated.');
    }
}

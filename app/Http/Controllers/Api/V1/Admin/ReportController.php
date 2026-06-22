<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ReportRepository;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportRepository $repository) {}

    public function summary(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        return ApiResponse::success(
            $this->repository->summary($data['from'] ?? null, $data['to'] ?? null),
            'Reports summary.',
        );
    }
}

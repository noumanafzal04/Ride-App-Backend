<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ReportRepository;
use App\Support\ApiResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportRepository $repository) {}

    public function summary()
    {
        return ApiResponse::success($this->repository->summary(), 'Reports summary.');
    }
}

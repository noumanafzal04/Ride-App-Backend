<?php

namespace App\Http\Requests\Api\V1\Inspection;

use App\Models\InspectionRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the `admin` middleware on the route
    }

    public function rules(): array
    {
        return [
            'status'             => ['required', 'string', Rule::in(InspectionRequest::STATUSES)],
            'scheduled_at'       => ['nullable', 'date'],
            'overall_grade'      => ['nullable', 'string', 'max:10'],
            'overall_score'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'inspector_comments' => ['nullable', 'string', 'max:5000'],
            'admin_notes'        => ['nullable', 'string', 'max:5000'],
        ];
    }
}

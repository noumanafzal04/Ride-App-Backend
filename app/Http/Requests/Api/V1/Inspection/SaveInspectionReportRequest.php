<?php

namespace App\Http\Requests\Api\V1\Inspection;

use App\Models\InspectionCategoryResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInspectionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the `admin` middleware on the route
    }

    public function rules(): array
    {
        return [
            'items'                => ['required', 'array', 'min:1'],
            'items.*.category_id'  => ['required', 'integer', 'exists:inspection_categories,id'],
            'items.*.condition'    => ['required', 'string', Rule::in(InspectionCategoryResult::CONDITIONS)],
            'items.*.notes'        => ['nullable', 'string', 'max:1000'],
            'comments'             => ['nullable', 'string', 'max:5000'],
        ];
    }
}

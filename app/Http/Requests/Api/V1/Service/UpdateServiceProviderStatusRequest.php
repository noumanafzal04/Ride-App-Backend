<?php

namespace App\Http\Requests\Api\V1\Service;

use App\Models\ServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceProviderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by `admin` middleware
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ServiceProvider::STATUSES)],
        ];
    }
}

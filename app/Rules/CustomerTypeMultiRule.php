<?php

namespace App\Rules;

use App\Models\Company\CompanyCustomerType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerTypeMultiRule implements ValidationRule
{

    public function __construct(
        private readonly int $companyId,
        private readonly bool $oneMustDefaultCheck = false
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {        
        $collection = collect($value);

        /**
         * Check single default
         */
        $defaultCount = $collection            
            ->where('is_default', true)
            ->count();

        if ($defaultCount !== 1) {
            $fail(__('company_customer_types.one_default_customer_type_per_company'));
        }

        if ($this->oneMustDefaultCheck && $defaultCount === 0) {
            $fail('company_customer_types.one_must_be_default');
        }

        /**
         * Check IDs exist (ONE QUERY)
         */
        $ids = $collection
            ->pluck('id')
            ->filter()
            ->unique();        

        $existingIds = CompanyCustomerType::where('company_id', $this->companyId)->whereIn('id', $ids)->pluck('id');

        $invalidIds = $ids->diff($existingIds);

        if ($invalidIds->isNotEmpty()) {
            $fail(
                __('company_customer_types.invalid_customer_type_id', ['ids' => $invalidIds->implode(', ')])
            );
        }        
    }
}

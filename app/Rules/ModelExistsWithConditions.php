<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ModelExistsWithConditions implements ValidationRule
{
    /**
     * @param class-string<Model> $modelClass
     * @param array<string, mixed> $conditions    
     */
    public function __construct(
        protected string $modelClass,
        protected array $conditions = [],
        protected ?Closure $callback = null, 
        protected ?string $message = null,       
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Model $model */
        $model = new $this->modelClass;

        $query = $model->newQuery();

        // Validate primary key
        $query->where($model->getKeyName(), $value);

        // Apply static conditions
        foreach ($this->conditions as $column => $condition) {
            if ($condition !== null) {
                $query->where($column, $condition);
            }
        }

        // Apply custom query callback
        if ($this->callback) {
            ($this->callback)($query);
        }

        if (! $query->exists()) {
            $fail($this->message ?? __('global.invalid_reference'));
        }
    }
}

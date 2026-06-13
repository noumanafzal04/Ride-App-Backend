<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ExistsInDbArray implements Rule
{
    protected string $table;
    protected string $column;
    protected array $conditions;
    protected array $missing = [];

    /**
     * @param string $table Table name
     * @param string $column Column to check (default 'id')
     * @param array $conditions Additional where conditions ['column' => value]
     */
    public function __construct(string $table, string $column = 'id', array $conditions = [])
    {
        $this->table = $table;
        $this->column = $column;
        $this->conditions = $conditions;
    }

    /**
     * Validate that all IDs exist with given conditions.
     */
    public function passes($attribute, $value): bool
    {
        // Ensure $value is an array of arrays (nested)
        if (!is_array($value)) {
            return false;
        }

        $ids = array_column($value, $this->column);

        $query = DB::table($this->table)
            ->whereIn($this->column, $ids);

        // Apply conditions like company_id, status, etc.
        foreach ($this->conditions as $col => $val) {
            $query->where($col, $val);
        }

        $existing = $query->pluck($this->column)->toArray();

        $this->missing = array_diff($ids, $existing);

        return empty($this->missing);
    }

    /**
     * Error message if validation fails.
     */
    public function message(): string
    {
        return 'The following ' . $this->column . '(s) do not exist or do not meet the required conditions: ' . implode(', ', $this->missing);
    }
}
<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class JsonHelper
{
   
    public static function updateJsonKey(        
        $id,        
        array $data,
        string $table = 'companies',
        string $jsonColumn = 'registration_progress',
        array $conditions = []
    ): int {
        $query = DB::table($table)->where('id', $id);

        // Apply extra conditions if provided
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        $updates = [];
        foreach ($data as $key => $value) {
            // check JSON keys (dot notation)
            if (str_contains($key, '.')) {

                // Convert dot notation to JSON path
                $jsonKey = str_replace('.', '->', $key);

                $updates["$jsonColumn->$jsonKey"] = $value;

            } else {                
                $updates[$key] = $value;
            }
        }

        return $query->update($updates);
    }
}
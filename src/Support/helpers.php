<?php

if (!function_exists('arrayIsEqualWithDB')) {
    /**
     * @param array $array
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $skippable
     * @return bool
     */
    function arrayIsEqualWithDB(array $array, \Illuminate\Database\Eloquent\Model $model, array $skippable = []): bool
    {
        $columns = $model->getFillable();
        foreach ($columns as $column) {
            if (in_array($column, $skippable)) continue;

            if (isset($array[$column])) {
                if ($array[$column] instanceof \Carbon\Carbon && !($model->{$column} instanceof \Carbon\Carbon)) {
                    $model->{$column} = \Carbon\Carbon::parse($model->{$column});
                }

                if ($model->{$column} != $array[$column]) {
                    return false;
                }
            } elseif ($model->{$column} !== null) {
                return false;
            }

        }
        return true;
    }
}
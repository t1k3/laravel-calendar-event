<?php
use Carbon\Carbon;
use Carbon\CarbonInterval;

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

if (!function_exists('getWeekdaysInMonth')) {
    /**
    * @param string $weekday
    * @param date $date
    * @return collection
    */
   function getWeekdaysInMonth($weekday, $date)
   {
       $next = $date->copy()->addMonths(1);
        return collect(new \DatePeriod(
       
           Carbon::parse("first $weekday of $date"),
           CarbonInterval::week(),
           Carbon::parse("first $weekday of $next")
       ));
    }
}
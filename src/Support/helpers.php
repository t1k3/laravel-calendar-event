<?php

/**
 * @param array $array
 * @param $db
 * @return bool
 */
if (!function_exists('arrayIsEqualWithDB')) {
    function arrayIsEqualWithDB(array $array, $db): bool
    {
        $db    = $db->toArray();
        $unset = ['id', 'created_at', 'updated_at', 'deleted_at'];
        foreach ($unset as $key) {
            if (isset($db[$key])) unset($db[$key]);
        }

        foreach ($array as $key => $value) {
            if($value instanceof \Carbon\Carbon && !($db[$key] instanceof \Carbon\Carbon)) {
                $db[$key] = \Carbon\Carbon::parse($db[$key]);
            }

            if ($db[$key] != $value) {
                return false;
            }
        }
        return true;
    }
}

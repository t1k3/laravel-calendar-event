<?php

/**
 * @param array $array
 * @param $db
 * @return bool
 */
function arrayIsEqualWithDB(array $array, $db): bool
{
    $db    = $db->toArray();
    $unset = ['id', 'created_at', 'updated_at', 'deleted_at'];
    foreach ($unset as $key) {
        if (isset($db[$key])) unset($db[$key]);
    }

    foreach ($array as $key => $value) {
        if ($db[$key] !== $value) {
            return true;
        }
    }
    return false;
}
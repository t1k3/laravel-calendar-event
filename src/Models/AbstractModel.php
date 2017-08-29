<?php

namespace T1k3\LaravelCalendarEvent\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class AbstractModel
 * @package T1k3\LaravelCalendarEvent\Models
 */
abstract class AbstractModel extends Model
{
    /**
     * Get specified attributes in array
     * @param array $keys
     * @return array
     */
    public function getAttributesArray(array $keys): array
    {
        $response   = [];
        $attributes = $this->getAttributes();
        foreach ($keys as $key) {
            if (isset($attributes[$key])) {
                $response[$key] = $attributes[$key];
            }
        }
        return $response;
    }

    /**
     * @param $query
     * @return Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}

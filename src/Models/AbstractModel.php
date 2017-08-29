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
     * @param $query
     * @return Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}

<?php

namespace T1k3\LaravelCalendarEvent\Interfaces;


/**
 * Interface PlaceInterface
 * @package T1k3\LaravelCalendarEvent\Interfaces
 */
interface PlaceInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasManyThrough
     */
    public function events();
}
<?php

namespace T1k3\LaravelCalendarEvent\Interfaces;


/**
 * Interface UserInterface
 * @package T1k3\LaravelCalendarEvent\Interfaces
 */
interface UserInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasManyThrough
     */
    public function calendarEvents();
}
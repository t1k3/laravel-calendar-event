<?php

namespace T1k3\LaravelCalendarEvent\Traits;


use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

/**
 * Trait UserTemplateCalendarEventTrait
 * @package T1k3\LaravelCalendarEvent\Traits
 */
trait CalendarEventUserTrait
{
    /**
     * Events to User, UserInterface Helper
     * @return mixed
     */
    public function events()
    {
        return $this->hasManyThrough(
            TemplateCalendarEvent::class, CalendarEvent::class,
            'template_calendar_event_id', 'user_id', 'id'
        );
    }
}
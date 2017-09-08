<?php

namespace T1k3\LaravelCalendarEvent\Traits;


use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

/**
 * Trait PlaceTemplateCalendarEventTrait
 * @package T1k3\LaravelCalendarEvent\Traits
 */
trait CalendarEventPlaceTrait
{
    /**
     * Events to Place, PlaceInterface Helper
     * @return mixed
     */
    public function events()
    {
        return $this->hasManyThrough(
            CalendarEvent::class, TemplateCalendarEvent::class,
            'place_id', 'template_calendar_event_id', 'id'
        );
    }
}
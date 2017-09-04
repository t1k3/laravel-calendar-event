<?php

namespace T1k3\LaravelCalendarEvent\Tests\fixture\app\Models;


use T1k3\LaravelCalendarEvent\Interfaces\PlaceInterface;
use T1k3\LaravelCalendarEvent\Models\AbstractModel;
use T1k3\LaravelCalendarEvent\Traits\CalendarEventPlaceTrait;

/**
 * Class Place
 * @package T1k3\LaravelCalendarEvent\Tests\Fixture\App\Models
 */
class Place extends AbstractModel implements PlaceInterface
{
 use CalendarEventPlaceTrait;
}
<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Traits;


use T1k3\LaravelCalendarEvent\Tests\fixture\app\Models\Place;
use T1k3\LaravelCalendarEvent\Tests\TestCase;

/**
 * Class CalendarEventPlaceTraitTest
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Traits
 */
class CalendarEventPlaceTraitTest extends TestCase
{
    /**
     * @test
     */
    public function event()
    {
        $place  = new Place();
        $events = $place->events();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $events);
    }
}
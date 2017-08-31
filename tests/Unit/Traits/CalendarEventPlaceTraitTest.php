<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Traits;

use T1k3\LaravelCalendarEvent\Models\AbstractModel;
use T1k3\LaravelCalendarEvent\Tests\TestCase;
use T1k3\LaravelCalendarEvent\Traits\CalendarEventPlaceTrait;

/**
 * Class Place | "Mock"
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Traits
 */
class Place extends AbstractModel
{
    use CalendarEventPlaceTrait;
}

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
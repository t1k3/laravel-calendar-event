<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Traits;


use T1k3\LaravelCalendarEvent\Tests\Fixtures\Models\User;
use T1k3\LaravelCalendarEvent\Tests\TestCase;

/**
 * Class CalendarEventUserTraitTest
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Traits
 */
class CalendarEventUserTraitTest extends TestCase
{
    /**
     * @test
     */
    public function event()
    {
        $user   = new User();
        $events = $user->events();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $events);
    }
}
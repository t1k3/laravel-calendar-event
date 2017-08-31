<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Traits;


use T1k3\LaravelCalendarEvent\Models\AbstractModel;
use T1k3\LaravelCalendarEvent\Tests\TestCase;
use T1k3\LaravelCalendarEvent\Traits\CalendarEventUserTrait;

/**
 * Class User | "Mock"
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Traits
 */
class User extends AbstractModel
{
    use CalendarEventUserTrait;
}

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
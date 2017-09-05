<?php

namespace T1k3\LaravelCalendarEvent\Tests\Fixtures\Models;

use T1k3\LaravelCalendarEvent\Interfaces\UserInterface;
use T1k3\LaravelCalendarEvent\Models\AbstractModel;
use T1k3\LaravelCalendarEvent\Traits\CalendarEventUserTrait;

/**
 * Class User
 * @package T1k3\LaravelCalendarEvent\Tests\Fixture\App\Models
 */
class User extends AbstractModel implements UserInterface
{
    use CalendarEventUserTrait;
}
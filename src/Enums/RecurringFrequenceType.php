<?php

namespace T1k3\LaravelCalendarEvent\Enums;

/**
 * Class RecurringFrequenceType
 * @package T1k3\LaravelCalendarEvent\Enums
 */
abstract class RecurringFrequenceType
{
    const DAY   = 'day';
    const WEEK  = 'week';
    const MONTH = 'month';
    const YEAR  = 'year';
    const NTHWEEKDAY  = 'nthweekday';
}
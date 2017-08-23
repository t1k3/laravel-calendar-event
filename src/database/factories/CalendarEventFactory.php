<?php

use Carbon\Carbon;

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(\T1k3\LaravelCalendarEvent\Models\CalendarEvent::class, function (Faker\Generator $faker) {
    return [
        'start_date' => Carbon::now()->addWeek()->format('Y-m-d')
    ];
});

<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;

$factory = app()->make(\Illuminate\Database\Eloquent\Factory::class);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(CalendarEvent::class, function (Faker $faker) {
    return [
        'start_date' => Carbon::now()->addWeek()->format('Y-m-d')
    ];
});

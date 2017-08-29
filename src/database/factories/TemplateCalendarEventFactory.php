<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

$factory = app()->make(\Illuminate\Database\Eloquent\Factory::class);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TemplateCalendarEvent::class, function (Faker $faker) {
    return [
        'start_date'   => Carbon::now()->addWeek()->format('Y-m-d'),
        'start_time'   => Carbon::now()->hour,
        'end_time'     => Carbon::now()->addHour()->hour,
        'description'  => str_random(32),
        'is_recurring' => false,
//        'frequence_number_of_recurring' => 1,
//        'frequence_type_of_recurring'   => 'week',
        'is_public'    => true,
    ];
});

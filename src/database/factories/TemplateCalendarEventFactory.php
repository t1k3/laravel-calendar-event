<?php

use Carbon\Carbon;

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(\T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent::class, function (Faker\Generator $faker) {
    return [
        'start_date'                    => Carbon::now()->addWeek()->format('Y-m-d'),
        'start_time'                    => Carbon::now()->hour,
        'end_time'                      => Carbon::now()->addHour()->hour,
        'description'                   => str_random(32),
        'is_recurring'                  => false,
//        'frequence_number_of_recurring' => 1,
//        'frequence_type_of_recurring'   => 'week',
        'is_public'                     => true,
    ];
});

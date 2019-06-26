<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Console\Command;

use Carbon\Carbon;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;
use T1k3\LaravelCalendarEvent\Tests\TestCase;

/**
 * Class GenerateCalendarEventTest
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Console\Command
 */
class GenerateCalendarEventTest extends TestCase
{
    /**
     * @var CalendarEvent
     */
    private $calendarEvent;

    /**
     * @var TemplateCalendarEvent
     */
    private $templateCalendarEvent;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->templateCalendarEvent = new TemplateCalendarEvent();
        $this->calendarEvent         = new CalendarEvent();
    }

    /**
     * @test
     */
    public function handle_notRecurring_notGenerated()
    {
        $now = '2017-08-28';
        $this->calendarEvent->createCalendarEvent([
            'start_date'   => '2017-08-29',
            'start_time'   => 8,
            'end_date'     => '2017-08-29',
            'end_time'     => 10,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $this->assertContains('Generated CalendarEvent from Console | Summary: 0', $this->getConsoleOutput());
        $this->assertEquals(1, $this->calendarEvent->all()->count());
    }

    /**
     * @test
     */
    public function handle_recurring_endOfRecurring_notGenerated()
    {
        $now           = '2017-08-09';
        $calendarEvent = $this->calendarEvent->createCalendarEvent([
            'start_date'                    => '2017-08-01',
            'start_time'                    => '10:00',
            'end_date'                      => '2017-08-01',
            'end_time'                      => '12:00',
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => '2017-08-14',
        ]);
        $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-08'
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $this->assertContains('Generated CalendarEvent from Console | Summary: 0', $this->getConsoleOutput());
        $this->assertEquals(2, $this->calendarEvent->all()->count());
    }

    public function dataProvider_for_handle_recurring_generated()
    {
        return [
            [
                [
                    'start_date'                    => '2017-08-01',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-01',
                    'end_time'                      => '12:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-16'),
                Carbon::parse('2017-08-16'),
                Carbon::parse('2017-08-16'),
            ],
            [
                [
                    'start_date'                    => '2017-08-01',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-01',
                    'end_time'                      => '12:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17'),
                Carbon::parse('2017-08-18'),
                Carbon::parse('2017-08-18'),
            ],
            [
                [
                    'start_date'                    => '2017-08-02',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-02',
                    'end_time'                      => '12:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 3,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17'),
                Carbon::parse('2017-08-19'),
                Carbon::parse('2017-08-19'),
            ],
            [
                [
                    'start_date'                    => '2017-08-01',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-01',
                    'end_time'                      => '12:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-16'),
                Carbon::parse('2017-08-22'),
                Carbon::parse('2017-08-22'),
            ],
            [
                [
                    'start_date'                    => '2017-08-02',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-04',
                    'end_time'                      => '10:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17'),
                Carbon::parse('2017-08-23'),
                Carbon::parse('2017-08-25'),
            ],
            [
                [
                    'start_date'                    => '2017-08-01',
                    'start_time'                    => '10:00',
                    'end_date'                      => '2017-08-02',
                    'end_time'                      => '10:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::MONTH,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-26'),
                Carbon::parse('2017-09-01'),
                Carbon::parse('2017-09-02'),
            ],
            [
                [
                    'start_date'                    => '2016-08-27',
                    'start_time'                    => '11:00',
                    'end_date'                      => '2016-08-28',
                    'end_time'                      => '01:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::YEAR,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-26'),
                Carbon::parse('2017-08-27'),
                Carbon::parse('2017-08-28'),
            ],
            [
                [
                    'start_date'                    => '2019-07-17',
                    'start_time'                    => '19:30',
                    'end_date'                      => '2019-07-17',
                    'end_time'                      => '20:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-07-15'),
                Carbon::parse('2019-07-17'),
                Carbon::parse('2019-07-17'),
            ],
            [
                [
                    'start_date'                    => '2019-08-21',
                    'start_time'                    => '19:30',
                    'end_date'                      => '2019-08-21',
                    'end_time'                      => '20:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-08-19'),
                Carbon::parse('2019-08-21'),
                Carbon::parse('2019-08-21'),
            ],
            [
                [
                    'start_date'                    => '2019-09-18',
                    'start_time'                    => '19:30',
                    'end_date'                      => '2019-09-18',
                    'end_time'                      => '20:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-09-16'),
                Carbon::parse('2019-09-18'),
                Carbon::parse('2019-09-18'),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_handle_recurring_generated
     * @param $input
     * @param $now
     * @param $startDate
     */
    public function handle_recurring_generated($input, $now, $startDate, $endDate)
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_date', 'desc')->first();

        $this->assertContains('Generated CalendarEvent from Console | Summary:', $this->getConsoleOutput());
        $this->assertEquals($startDate, $calendarEventLast->start_date);
        $this->assertEquals($endDate, $calendarEventLast->end_date);

        $this->assertDatabaseHas('calendar_events', ['start_date' => $startDate, 'end_date' => $endDate]);
    }

    /**
     * @test
     */
    public function handle_recurring_editedNotRecurring_notGenerated()
    {
        $now                         = Carbon::parse('2017-08-14');
        $calendarEvent               = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => Carbon::parse('2017-08-01'),
            'start_time'                    => Carbon::parse('10:00'),
            'end_date'                      => Carbon::parse('2017-08-01'),
            'end_time'                      => Carbon::parse('12:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(new \DateTime($now)); // '2017-08-08'
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-15'
        $calendarEventNextTmpUpdated = $calendarEventNextTmp->editCalendarEvent([
            'start_date'   => Carbon::parse('2017-08-09'),
            'is_recurring' => false,
        ]);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_date', 'desc')->first();

        // The next is 2017-08-15 but is deleted
        $this->assertContains('Generated CalendarEvent from Console | Summary: 0', $this->getConsoleOutput());
        $this->assertEquals('2017-08-08', $calendarEventLast->start_date->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function handle_recurring_editedNotRecurring_generated()
    {
        $now                         = Carbon::parse('2017-08-16');
        $calendarEvent               = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => Carbon::parse('2017-08-01'),
            'start_time'                    => Carbon::parse('10:00'),
            'end_date'                      => Carbon::parse('2017-08-01'),
            'end_time'                      => Carbon::parse('12:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(new \DateTime($now)); // '2017-08-08'
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-15'
        $calendarEventNextTmpUpdated = $calendarEventNextTmp->editCalendarEvent([
            'start_date'   => Carbon::parse('2017-08-09'),
            'is_recurring' => false,
        ]);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_date', 'desc')->first();

        $this->assertContains('Generated CalendarEvent from Console | Summary:', $this->getConsoleOutput());
        $this->assertEquals('2017-08-22', $calendarEventLast->start_date->format('Y-m-d'));

        $this->assertDatabaseHas('calendar_events', ['start_date' => Carbon::parse('2017-08-22')]);
    }
}
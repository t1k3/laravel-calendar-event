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
            'start_datetime'   => '2017-08-29 08:00:00',
            'end_datetime'     => '2017-08-29 10:00:00',
            'description'      => str_random(32),
            'is_recurring'     => false,
            'is_public'        => true,
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
            'start_datetime'                => '2017-08-01 10:00:00',
            'end_datetime'                  => '2017-08-01 12:00:00',
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
                    'start_datetime'                => '2017-08-01 10:00:00',
                    'end_datetime'                  => '2017-08-01 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-16 10:00:00'),
                Carbon::parse('2017-08-16 10:00:00'),
                Carbon::parse('2017-08-16 10:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2017-08-01 10:00:00',
                    'end_datetime'                  => '2017-08-01 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17 10:00:00'),//might need to be 2017-08-18
                Carbon::parse('2017-08-18 10:00:00'),
                Carbon::parse('2017-08-18 10:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2017-08-02 10:00:00',
                    'end_datetime'                  => '2017-08-02 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 3,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17 10:00:00'),
                Carbon::parse('2017-08-19 10:00:00'),
                Carbon::parse('2017-08-19 10:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2017-08-01 10:00:00',
                    'end_datetime'                  => '2017-08-01 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-16 10:00:00'),
                Carbon::parse('2017-08-22 10:00:00'),
                Carbon::parse('2017-08-22 10:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2017-08-02 10:00:00',
                    'end_datetime'                  => '2017-08-04 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-17 10:00:00'),
                Carbon::parse('2017-08-23 10:00:00'),
                Carbon::parse('2017-08-25 10:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2017-08-01 10:00:00',
                    'end_datetime'                  => '2017-08-02 12:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::MONTH,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-26 10:00:00'),
                Carbon::parse('2017-09-01 10:00:00'),
                Carbon::parse('2017-09-02 12:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2016-08-27 11:00:00',
                    'end_datetime'                  => '2016-08-28 01:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::YEAR,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-26 11:00:00'),
                Carbon::parse('2017-08-27 11:00:00'),
                Carbon::parse('2017-08-28 11:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2019-07-17 19:30:00',
                    'end_datetime'                  => '2019-07-17 20:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-07-15 19:30:00'),
                Carbon::parse('2019-07-17 19:30:00'),
                Carbon::parse('2019-07-17 20:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2019-08-21 19:30:00',
                    'end_datetime'                  => '2019-08-21 20:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-08-19 19:30:00'),
                Carbon::parse('2019-08-21 19:30:00'),
                Carbon::parse('2019-08-21 20:00:00'),
            ],
            [
                [
                    'start_datetime'                => '2019-09-18 19:30:00',
                    'end_datetime'                  => '2019-09-18 20:00:00',
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2019-09-16 19:30:00'),
                Carbon::parse('2019-09-18 19:30:00'),
                Carbon::parse('2019-09-18 20:00:00'),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_handle_recurring_generated
     * @param $input
     * @param $now
     * @param $startDateTime
     */
    public function handle_recurring_generated($input, $now, $startDateTime, $endDateTime)
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_datetime', 'desc')->first();

        $this->assertContains('Generated CalendarEvent from Console | Summary:', $this->getConsoleOutput());
        $this->assertEquals(Carbon::parse($startDateTime), Carbon::parse($calendarEventLast->start_datetime));
        $this->assertEquals(Carbon::parse($endDateTime), Carbon::parse($calendarEventLast->end_datetime));
        $this->assertDatabaseHas('calendar_events', ['start_datetime' => Carbon::parse($startDateTime), 'end_datetime' => Carbon::parse($endDateTime)]);
    }

    /**
     * @test
     */
    public function handle_recurring_editedNotRecurring_notGenerated()
    {
        $now                         = Carbon::parse('2017-08-14');
        $calendarEvent               = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_datetime'                => Carbon::parse('2017-08-01 10:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-01 12:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(new \DateTime($now)); // '2017-08-08'
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-15'
        $calendarEventNextTmpUpdated = $calendarEventNextTmp->editCalendarEvent([
            'start_datetime'   => Carbon::parse('2017-08-09 10:00:00'),
            'is_recurring' => false,
        ]);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_datetime', 'desc')->first();

        // The next is 2017-08-15 but is deleted
        $this->assertContains('Generated CalendarEvent from Console | Summary: 0', $this->getConsoleOutput());
        $this->assertEquals('2017-08-08', $calendarEventLast->start_datetime->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function handle_recurring_editedNotRecurring_generated()
    {
        $now                         = Carbon::parse('2017-08-16');
        $calendarEvent               = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_datetime'                => Carbon::parse('2017-08-01 10:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-01 12:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(new \DateTime($now)); // '2017-08-08'
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-15'
        $calendarEventNextTmpUpdated = $calendarEventNextTmp->editCalendarEvent([
            'start_datetime'   => Carbon::parse('2017-08-09 10:00:00'),
            'is_recurring' => false,
        ]);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_datetime', 'desc')->first();

        $this->assertContains('Generated CalendarEvent from Console | Summary:', $this->getConsoleOutput());
        $this->assertEquals('2017-08-22', $calendarEventLast->start_datetime->format('Y-m-d'));

        $this->assertDatabaseHas('calendar_events', ['start_datetime' => Carbon::parse('2017-08-22 10:00:00')]);
    }
}
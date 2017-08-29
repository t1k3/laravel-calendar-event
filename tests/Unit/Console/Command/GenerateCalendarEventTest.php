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
            'end_time'     => 10,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ]);
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $this->assertContains('Generated: next calendar events.', $this->getConsoleOutput());
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
            'start_time'                    => 10,
            'end_time'                      => 12,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => '2017-08-14',
        ]);
        $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-08'
        $this->artisan('generate:calendar-event', ['--date' => $now]);

        $this->assertContains('Generated: next calendar events.', $this->getConsoleOutput());
        $this->assertEquals(2, $this->calendarEvent->all()->count());
    }

    /**
     * @test
     */
    public function handle_recurring_editedNotRecurring_generated()
    {
        $now                         = '2017-08-09';
        $calendarEvent               = $this->calendarEvent->createCalendarEvent([
            'start_date'                    => '2017-08-01',
            'start_time'                    => 10,
            'end_time'                      => 12,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(new \DateTime($now)); // '2017-08-08'
        $calendarEventNextTmp        = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse($now)); // '2017-08-15'
        $calendarEventNextTmpUpdated = $calendarEventNextTmp->editCalendarEvent([
            'start_date'   => '2017-08-09',
            'is_recurring' => false
        ]);

        $this->artisan('generate:calendar-event', ['--date' => $now]);
        $calendarEventLast = $calendarEvent->template->events()->orderBy('start_date', 'desc')->first();

        $this->assertContains('Generated: next calendar events.', $this->getConsoleOutput());
        $this->assertEquals('2017-08-22', $calendarEventLast->start_date->format('Y-m-d'));
    }
}
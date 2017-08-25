<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Models;

use Carbon\Carbon;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;
use T1k3\LaravelCalendarEvent\Tests\TestCase;

/**
 * Class TemplateCalendarEventTest
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Models
 */
class TemplateCalendarEventTest extends TestCase
{
    /**
     * @var  TemplateCalendarEvent $templateCalendarEvent
     */
    private $templateCalendarEvent;

    /**
     * @var CalendarEvent $calendarEvent
     */
    private $calendarEvent;

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
    public function createInstanceFromClass()
    {
        $this->assertInstanceOf(TemplateCalendarEvent::class, $this->templateCalendarEvent);
    }

    /**
     * @test
     */
    public function getFillable()
    {
        $expectedFillables = [
            'start_date',
            'start_time',
            'end_time',
            'description',
            'is_recurring',
            'end_of_recurring',
            'frequence_number_of_recurring',
            'frequence_type_of_recurring',
            'is_public',
        ];

        $this->assertArraySubset($expectedFillables, $this->templateCalendarEvent->getFillable());
    }

    /**
     * @test
     */
    public function events()
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create();
        $calendarEvent         = factory(CalendarEvent::class)->make();
        $calendarEvent->template()->associate($templateCalendarEvent);
        $calendarEvent->save();

        $this->assertInstanceOf(CalendarEvent::class, $templateCalendarEvent->events()->first());
    }

    /**
     * @test
     */
    public function parent()
    {
        $templateCalendarEvent      = factory(TemplateCalendarEvent::class)->create();
        $templateCalendarEventChild = factory(TemplateCalendarEvent::class)->create();
        $templateCalendarEventChild->parent()->associate($templateCalendarEvent);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $templateCalendarEventChild->parent);
    }

    /**
     * Data provider for generate next calendar event
     * @return array
     */
    public function dataProvider_for_generateNextCalendarEvent()
    {
        return [
            [4, RecurringFrequenceType::DAY, Carbon::now()->addDays(4)],
            [2, RecurringFrequenceType::WEEK, Carbon::now()->addWeek(2)],
            [3, RecurringFrequenceType::MONTH, Carbon::now()->addMonths(3)],
            [1, RecurringFrequenceType::YEAR, Carbon::now()->addYears(1)],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_generateNextCalendarEvent
     * @param $frequence_number_of_recurring
     * @param $frequence_type_of_recurring
     * @param $calendarEventNext_startDate
     */
    public function generateNextCalendarEvent($frequence_number_of_recurring, $frequence_type_of_recurring, $calendarEventNext_startDate)
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create([
            'start_date'                    => date('Y-m-d'),
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => $frequence_number_of_recurring,
            'frequence_type_of_recurring'   => $frequence_type_of_recurring,
            'is_public'                     => true,
        ]);
        $calendarEvent         = factory(CalendarEvent::class)->make(['start_date' => date('Y-m-d')]);
        $calendarEvent->template()->associate($templateCalendarEvent);
        $calendarEvent->save();

        $calendarEventNext = $templateCalendarEvent->generateNextCalendarEvent();

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventNext);
        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventNext->template);
        $this->assertEquals($calendarEvent->template->id, $calendarEventNext->template->id);
        $this->assertEquals($calendarEventNext_startDate->format('Y-m-d'), $calendarEventNext->start_date->format('Y-m-d'));
    }
}

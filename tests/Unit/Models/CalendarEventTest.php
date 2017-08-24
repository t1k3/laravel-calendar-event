<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Models;

use Carbon\Carbon;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;
use T1k3\LaravelCalendarEvent\Tests\TestCase;

/**
 * Class CalendarEventTest
 * @package T1k3\LaravelCalendarEvent\Tests\Unit\Models
 */
class CalendarEventTest extends TestCase
{
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
        $this->calendarEvent = new CalendarEvent();
    }

    /**
     * @test
     */
    public function createInstanceFromClass()
    {
        $this->assertInstanceOf(CalendarEvent::class, $this->calendarEvent);
    }

    /**
     * @test
     */
    public function getFillable()
    {
        $expectedFillables = [
            'start_date'
        ];
        $this->assertArraySubset($expectedFillables, $this->calendarEvent->getFillable());
    }

    /**
     * @test
     */
    public function template()
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create();

        $calendarEvent = factory(CalendarEvent::class)->make();
        $calendarEvent->template()->associate($templateCalendarEvent);
        $calendarEvent->save();

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEvent->template);
    }

    /**
     * Data provider for create calendar event
     * @return array
     */
    public function dataProvider_for_createCalendarEvent()
    {
        return [
            'not_recurring' => [
                [
                    'start_date'   => date('Y-m-d'),
                    'start_time'   => 10,
                    'end_time'     => 12,
                    'description'  => str_random(32),
                    'is_recurring' => false,
                    'is_public'    => true,
                ],
                date('Y-m-d')
            ],
            'recurring'     => [
                [
                    'start_date'   => date('Y-m-d'),
                    'start_time'   => 10,
                    'end_time'     => 12,
                    'description'  => str_random(32),
                    'is_recurring' => true,
                    'is_public'    => true,
                ],
                null
            ],
        ];
    }

    /**
     * Create event, not recurring
     * @test
     * @dataProvider dataProvider_for_createCalendarEvent
     * @param array $input
     * @param null|string $end_of_recurring
     */
    public function createCalendarEvent(array $input, $end_of_recurring)
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertEquals($input['start_date'], $calendarEvent->start_date);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEvent->template);
        $this->assertEquals($end_of_recurring, $calendarEvent->template->end_of_recurring);
        $this->assertArraySubset($input, $calendarEvent->template->toArray());
    }

    /**
     * Edit event but not modified
     * @test
     */
    public function editCalendarEvent_notModified()
    {
        $input                = [
            'start_date'   => Carbon::now()->addWeek()->format('Y-m-d'),
            'start_time'   => Carbon::now()->hour,
            'end_time'     => Carbon::now()->addHour()->hour,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,

        ];
        $calendarEvent        = $this->calendarEvent->createCalendarEvent($input);
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($input);

        $this->assertNull($calendarEventUpdated);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_notRecurring_modifiedCalendarEventData()
    {
        $calendarEvent     = $this->calendarEvent->createCalendarEvent([
            'start_date'                    => Carbon::now()->addWeek()->format('Y-m-d'),
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => 'week',
            'is_public'                     => true,
        ]);

//        TODO Add generateCalendarEvent method
        $calendarEventNext = $this->calendarEvent->make(['start_date' => Carbon::now()->addWeek(2)->format('Y-m-d')]);
        $calendarEventNext->template()->associate($calendarEvent->template);
        $calendarEventNext->save();

        $input                = [
            'start_date' => Carbon::now()->addWeek(3)->format('Y-m-d')
        ];
        $calendarEventUpdated = $calendarEventNext->editCalendarEvent($input);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertEquals($calendarEventNext->start_date, $calendarEventNext->template->end_of_recurring);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($input['start_date'], $calendarEventUpdated->start_date);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_notRecurring_modifiedToRecurring()
    {
        $calendarEvent        = $this->calendarEvent->createCalendarEvent([
            'start_date'   => Carbon::now()->addWeek()->format('Y-m-d'),
            'start_time'   => Carbon::now()->hour,
            'end_time'     => Carbon::now()->addHour()->hour,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ]);
        $input                = [
            'is_recurring' => true
        ];
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($input);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($input['is_recurring'], $calendarEventUpdated->template->is_recurring);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);
    }
}

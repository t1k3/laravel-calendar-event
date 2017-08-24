<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Models;

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
     * @test
     */
    public function createEvent()
    {
        $input = [
            'start_date'   => date('Y-m-d'),
            'start_time'   => 10,
            'end_time'     => 12,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,

        ];
        $event = $this->calendarEvent->createEvent($input);

        $this->assertInstanceOf(CalendarEvent::class, $event);
        $this->assertEquals($input['start_date'], $event->start_date);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $event->template);
        $this->assertArraySubset($input, $event->template->toArray());
    }

    /**
     * Edit event but not modified
     * @test
     */
    public function editEvent_notModified_false()
    {
        $input         = [
            'start_date'   => date('Y-m-d'),
            'start_time'   => 10,
            'end_time'     => 12,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,

        ];
        $calendarEvent = $this->calendarEvent->createEvent($input);
        $updated       = $calendarEvent->editEvent($input);

        $this->assertNull($updated);
    }

    /**
     * @test
     */
    public function editEvent()
    {
        $input                = [
            'start_date'                    => date('Y-m-d'),
            'start_time'                    => 10,
            'end_time'                      => 12,
            'description'                   => str_random(32),
            'is_recurring'                  => false,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => 'week',
            'is_public'                     => true,

        ];
        $calendarEvent        = $this->calendarEvent->createEvent($input);
        $calendarEventUpdated = $calendarEvent->editEvent(array_merge($input, ['start_time' => 11, 'end_time' => 12]));

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertNotEquals($calendarEvent, $calendarEventUpdated);
        $this->assertEquals(11, $calendarEventUpdated->template->start_time);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);
    }
}

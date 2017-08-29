<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Models;

use Carbon\Carbon;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
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
                ]
            ],
            'recurring'     => [
                [
                    'start_date'                    => date('Y-m-d'),
                    'start_time'                    => 10,
                    'end_time'                      => 12,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ]
            ],
        ];
    }

    /**
     * Create event, not recurring
     * @test
     * @dataProvider dataProvider_for_createCalendarEvent
     * @param array $input
     */
    public function createCalendarEvent(array $input)
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertEquals($input['start_date'], $calendarEvent->start_date->format('Y-m-d'));

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEvent->template);
//        $this->assertArraySubset($input, $calendarEvent->template->toArray());
    }

    /**
     * Edit event but not modified
     * @test
     */
    public function editCalendarEvent_notModified()
    {
        $input                = [
            'start_date'   => '2017-08-01',
            'start_time'   => 11,
            'end_time'     => 12,
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
    public function editCalendarEvent_notRecurring_modifiedCalendarEventData_recurring()
    {
        $inputCreate          = [
            'start_date'                    => '2017-08-01',
            'start_time'                    => 11,
            'end_time'                      => 12,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => '2017-08-22',
        ];
        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext    = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-06'));
        $inputUpdate          = [
            'start_date' => '2017-08-03'
        ];
        $calendarEventUpdated = $calendarEventNext->editCalendarEvent($inputUpdate);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertEquals($calendarEventNext->start_date, $calendarEventNext->template->end_of_recurring);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($inputUpdate['start_date'], $calendarEventUpdated->start_date->format('Y-m-d'));
        $this->assertEquals($inputCreate['end_of_recurring'], $calendarEventUpdated->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_notRecurring_modifiedCalendarEventData_notRecurring()
    {
        $inputCreate          = [
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => '2017-09-08'
        ];
        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext    = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-27'));
        $inputUpdate          = [
            'start_date'   => '2017-08-27',
            'is_recurring' => false,
        ];
        $calendarEventUpdated = $calendarEventNext->editCalendarEvent($inputUpdate);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertEquals($inputCreate['end_of_recurring'], $calendarEventNext->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($inputUpdate['start_date'], $calendarEventUpdated->start_date->format('Y-m-d'));
        $this->assertNull($calendarEventUpdated->template->end_of_recurring);

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
            'start_date'   => '2017-08-25',
            'start_time'   => 8,
            'end_time'     => 14,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ]);
        $inputUpdate          = [
            'is_recurring' => true
        ];
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($inputUpdate);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($inputUpdate['is_recurring'], $calendarEventUpdated->template->is_recurring);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);
    }


    /**
     * NOT data provider
     * @return array
     */
    public function data_for_eventOfMonth()
    {
        return [
            [
                'start_date'   => '2017-07-14',
                'start_time'   => Carbon::now()->hour,
                'end_time'     => Carbon::now()->addHour()->hour,
                'description'  => str_random(32),
                'is_recurring' => false,
                'is_public'    => true,
            ],
            [
                // 08: 05, 12, 19, 26
                'start_date'                    => '2017-07-15',
                'start_time'                    => Carbon::now()->hour,
                'end_time'                      => Carbon::now()->addHour()->hour,
                'description'                   => str_random(32),
                'is_recurring'                  => true,
                'frequence_number_of_recurring' => 1,
                'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                'is_public'                     => true,
            ],
            [
                // 08: 02
                'start_date'   => '2017-08-02',
                'start_time'   => Carbon::now()->hour,
                'end_time'     => Carbon::now()->addHour()->hour,
                'description'  => str_random(32),
                'is_recurring' => false,
                'is_public'    => true,
            ],
            [
                // 08: 03, 10, 17, 24, 31
                'start_date'                    => '2017-08-03',
                'start_time'                    => Carbon::now()->hour,
                'end_time'                      => Carbon::now()->addHour()->hour,
                'description'                   => str_random(32),
                'is_recurring'                  => true,
                'frequence_number_of_recurring' => 1,
                'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                'is_public'                     => true,
            ],
            [
                // 08: 05, 12, 19, 26
                'start_date'                    => '2017-08-05',
                'start_time'                    => Carbon::now()->hour,
                'end_time'                      => Carbon::now()->addHour()->hour,
                'description'                   => str_random(32),
                'is_recurring'                  => true,
                'frequence_number_of_recurring' => 1,
                'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                'is_public'                     => true,
                'end_of_recurring'              => '2017-09-25',
            ],
            [
                'start_date'                    => '2017-09-01',
                'start_time'                    => Carbon::now()->hour,
                'end_time'                      => Carbon::now()->addHour()->hour,
                'description'                   => str_random(32),
                'is_recurring'                  => true,
                'frequence_number_of_recurring' => 1,
                'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                'is_public'                     => true,
                'end_of_recurring'              => '2017-09-22',
            ]
        ];
    }

    /**
     * @test
     */
    public function eventsOfMonth()
    {
        $inputs = $this->data_for_eventOfMonth();
        foreach ($inputs as $input) {
            $this->calendarEvent->createCalendarEvent($input);
        }

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(8);
        $this->assertInstanceOf(CalendarEvent::class, $calendarEvents[0]);
//        $this->assertEquals(4, $calendarEvents->count());
        $this->assertEquals(14, $calendarEvents->count());
    }

    /**
     * Data provider for eventsOfMonh_InvalidMonthException
     * @return array
     */
    public function dataProvider_for_eventsOfMonh_InvalidMonthException()
    {
        return [
            [0],
            [13],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_eventsOfMonh_InvalidMonthException
     * @expectedException \T1k3\LaravelCalendarEvent\Exceptions\InvalidMonthException
     * @param $month
     */
    public function eventsOfMonh_InvalidMonthException($month)
    {
        CalendarEvent::showPotentialCalendarEventsOfMonth($month);
    }
}

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
                    'title'        => 'Lorem ipsum',
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
                    'title'                         => 'Lorem ipsum',
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
            'title'        => 'Lorem ipsum',
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
            'title'                         => 'Lorem ipsum',
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
            'title'                         => 'Lorem ipsum',
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
            'title'        => 'Lorem ipsum',
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
            'dates'  => [
                '2017-08-12', '2017-08-26',
                '2017-08-02',
                '2017-08-03', '2017-08-10', '2017-08-17', '2017-08-24', '2017-08-31',
                '2017-08-04', '2017-08-18',
                '2017-08-06',
            ],
            'inputs' => [
                [
                    'title'                         => 'Lorem ipsum',
                    'start_date'                    => '2016-10-01',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::YEAR,
                    'is_public'                     => true,
                ],
                [
                    'title'        => 'Lorem ipsum',
                    'start_date'   => '2017-07-14',
                    'start_time'   => Carbon::now()->hour,
                    'end_time'     => Carbon::now()->addHour()->hour,
                    'description'  => str_random(32),
                    'is_recurring' => false,
                    'is_public'    => true,
                ],
                [
                    // 2017-08-12, 2017-08-26
                    'title'                         => 'Lorem ipsum',
                    'start_date'                    => '2017-07-15',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                [
                    // 2017-08-02
                    'title'        => 'Lorem ipsum',
                    'start_date'   => '2017-08-02',
                    'start_time'   => Carbon::now()->hour,
                    'end_time'     => Carbon::now()->addHour()->hour,
                    'description'  => str_random(32),
                    'is_recurring' => false,
                    'is_public'    => true,
                ],
                [
                    // 2017-08-03, 2017-08-10, 2017-08-17, 2017-08-24, 2017-08-31
                    'title'                         => 'Lorem ipsum',
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
                    // 2017-08-04, 2017-08-18
                    'title'                         => 'Lorem ipsum',
                    'start_date'                    => '2017-08-04',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                    'end_of_recurring'              => '2017-09-25',
                ],
                [
                    // 2017-08-06
                    'title'                         => 'Lorem ipsum',
                    'start_date'                    => '2017-07-06',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::MONTH,
                    'is_public'                     => true,
                ],
                [
                    'title'                         => 'Lorem ipsum',
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
            ]
        ];
    }

    /**
     * @test
     */
    public function eventsOfMonth()
    {
        $data = $this->data_for_eventOfMonth();
        extract($data); // $inputs, $dates

        foreach ($inputs as $input) {
            $this->calendarEvent->createCalendarEvent($input);
        }
        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2017-08'));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvents[0]);
        $this->assertEquals(count($dates), $calendarEvents->count());
        foreach ($calendarEvents as $calendarEvent) {
            $isExist = in_array($calendarEvent->start_date->format('Y-m-d'), $dates);
            $this->assertTrue($isExist);
        }
    }

    /**
     * @test
     */
    public function eventsOfMonth_empty()
    {
        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2017-08'));
        $this->assertEquals(0, $calendarEvents->count());
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_notRecurring_recurring()
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent([
            'title'        => 'Lorem ipsum',
            'start_date'   => '2017-08-25',
            'start_time'   => 16,
            'end_time'     => 17,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true
        ]);

        $calendarEvent->deleteCalendarEvent(true);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_notRecurring()
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $calendarEvent->deleteCalendarEvent(false);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_deleted()
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $calendarEvent->deleteCalendarEvent(true);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEvent->start_date, $calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_deleted_withoutInput()
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $calendarEvent->deleteCalendarEvent();

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEvent->start_date, $calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_notDeleted()
    {
        $calendarEvent     = $this->calendarEvent->createCalendarEvent([
            'title'                         => 'Lorem ipsum',
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $calendarEventNext = $calendarEvent->template->generateNextCalendarEvent(Carbon::now());
        $calendarEventNext->deleteCalendarEvent(true);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEventNext->start_date, $calendarEvent->template->end_of_recurring);
    }
}

<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\Models;

use Carbon\Carbon;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;
use T1k3\LaravelCalendarEvent\Tests\Fixtures\Models\Place;
use T1k3\LaravelCalendarEvent\Tests\Fixtures\Models\User;
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
            'start_datetime',
            'end_datetime',
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
                    'title'            => str_random(16),
                    'start_datetime'   => Carbon::parse('2017-08-25 10:00:00'),
                    'end_datetime'     => Carbon::parse('2017-08-25 12:00:00'),
                    'description'      => str_random(32),
                    'is_recurring'     => false,
                    'is_public'        => true,
                ],
            ],
            'recurring'     => [
                [
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-08-25 10:00:00'),
                    'end_datetime'                  => Carbon::parse('2017-08-25 12:00:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
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
        $this->assertEquals($input['start_datetime']->format('Y-m-d'), $calendarEvent->start_datetime->format('Y-m-d'));
        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEvent->template);

        $this->assertDatabaseHas('template_calendar_events', $input);
    }

    /**
     * @test
     * @dataProvider dataProvider_for_createCalendarEvent
     * @param array $input
     */
    public function createCalendarEvent_user_place(array $input)
    {
        $this->app['config']->set('calendar-event.user.model', User::class);
        $this->app['config']->set('calendar-event.place.model', Place::class);

        $user          = factory(config('calendar-event.user.model'))->create();
        $place         = factory(config('calendar-event.place.model'))->create();
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input, $user, $place);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertEquals($input['start_datetime']->format('Y-m-d'), $calendarEvent->start_datetime->format('Y-m-d'));
        $this->assertEquals($user, $calendarEvent->template->user);
        $this->assertEquals($place, $calendarEvent->template->place);
        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEvent->template);

        $this->assertInstanceOf(CalendarEvent::class, $user->calendarEvents()->first());
        $this->assertInstanceOf(CalendarEvent::class, $place->calendarEvents()->first());

        $this->assertDatabaseHas('template_calendar_events', $input);
        $this->assertDatabaseHas('users', $user->toArray());
        $this->assertDatabaseHas('places', $place->toArray());
    }

    /**
     * Edit event but not modified
     * @test
     */
    public function editCalendarEvent_notModified()
    {
        $inputCreate           = [
            'title'            => str_random(16),
            'start_datetime'   => Carbon::parse('2017-08-01 11:00:00'),
            'end_datetime'     => Carbon::parse('2017-08-01 12:00:00'),
            'description'      => str_random(32),
            'is_recurring'     => false,
            'is_public'        => true,

        ];
        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($inputCreate);

        $this->assertNull($calendarEventUpdated);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_recurring_modifiedCalendarEventData_recurring()
    {
        $inputCreate = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-01 11:00'),
            'end_datetime'                  => Carbon::parse('2017-08-01 12:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => Carbon::parse('2017-08-22'),
        ];
        $inputUpdate = [
            'start_datetime' => Carbon::parse('2017-08-03'),
        ];

        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext    = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-06'));
        $calendarEventUpdated = $calendarEventNext->editCalendarEvent($inputUpdate);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertEquals($calendarEventNext->start_datetime->format('Y-m-d'), $calendarEventNext->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($inputUpdate['start_datetime']->format('Y-m-d'), $calendarEventUpdated->start_datetime->format('Y-m-d'));
        $this->assertEquals($inputCreate['end_of_recurring']->format('Y-m-d'), $calendarEventUpdated->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);

        $this->assertDatabaseHas('template_calendar_events', $inputUpdate);
        $this->assertDatabaseHas('calendar_events', $inputUpdate);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_recurring_modifiedCalendarEventData_recurring_nextCalendarEventIsDeleted()
    {
        $inputCreate = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-01 11:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-01 12:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => Carbon::parse('2017-08-22'),
        ];
        $inputUpdate = [
            'start_datetime' => Carbon::parse('2017-08-03'),
        ];

        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext    = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-06'));
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($inputUpdate);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->fresh()->deleted_at);
        $this->assertEquals($calendarEvent->start_datetime->format('Y-m-d'), $calendarEventNext->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($inputUpdate['start_datetime']->format('Y-m-d'), $calendarEventUpdated->start_datetime->format('Y-m-d'));
        $this->assertEquals($inputCreate['end_of_recurring']->format('Y-m-d'), $calendarEventUpdated->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);

        $this->assertDatabaseHas('template_calendar_events', $inputUpdate);
        $this->assertDatabaseHas('calendar_events', $inputUpdate);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_recurring_modifiedCalendarEventData_notRecurring()
    {
        $inputCreate = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => Carbon::parse('2017-09-08'),
        ];
        $inputUpdate = [
            'start_datetime'  => Carbon::parse('2017-08-27'),
            'is_recurring'    => false,
        ];

        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext    = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-27'));
        $calendarEventUpdated = $calendarEventNext->editCalendarEvent($inputUpdate);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertEquals($inputCreate['end_of_recurring']->format('Y-m-d'), $calendarEventNext->template->end_of_recurring->format('Y-m-d'));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);
        $this->assertEquals($inputUpdate['start_datetime']->format('Y-m-d'), $calendarEventUpdated->start_datetime->format('Y-m-d'));
        $this->assertNull($calendarEventUpdated->template->end_of_recurring);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);

        $this->assertDatabaseHas('template_calendar_events', $inputUpdate);
        $this->assertDatabaseHas('calendar_events', ['start_datetime' => $inputUpdate['start_datetime']]);
    }

    /**
     * Edit event and modified, calendar event data
     * @test
     */
    public function editCalendarEvent_notRecurring_modifiedToRecurring()
    {
        $inputCreate = [
            'title'           => str_random(16),
            'start_datetime'  => Carbon::parse('2017-08-25 08:00:00'),
            'end_datetime'    => Carbon::parse('2017-08-25 14:00:00'),
            'description'     => str_random(32),
            'is_recurring'    => false,
            'is_public'       => true,
        ];
        $inputUpdate = [
            'is_recurring' => true,
        ];

        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventUpdated = $calendarEvent->editCalendarEvent($inputUpdate);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventUpdated);

        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventUpdated->template);
        $this->assertEquals($inputUpdate['is_recurring'], $calendarEventUpdated->template->is_recurring);
        $this->assertEquals($calendarEvent->id, $calendarEventUpdated->template->parent_id);

        $this->assertDatabaseHas('template_calendar_events', array_merge($inputCreate, $inputUpdate));
    }

    /**
     * @test
     */
    public function updateCalendarEvent()
    {
        $inputCreate = [
            'title'           => str_random(16),
            'start_datetime'  => Carbon::parse('2017-08-25 08:00:00'),
            'end_datetime'    => Carbon::parse('2017-08-25 14:00:00'),
            'description'     => str_random(32),
            'is_recurring'    => false,
            'is_public'       => true,
        ];

        $calendarEvent        = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventUpdated = $calendarEvent->updateCalendarEvent($inputCreate);

        $this->assertDatabaseHas('template_calendar_events', $inputCreate + ['id' => $calendarEventUpdated->template->id]);
        $this->assertDatabaseHas('calendar_events', ['id' => $calendarEventUpdated->id]);
        $this->assertNotEquals($calendarEvent->id, $calendarEventUpdated->id);
        $this->assertEquals($calendarEvent->template->id, $calendarEventUpdated->template->parent_id);
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
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2016-10-01 11:30:00'),
                    'end_datetime'                  => Carbon::parse('2016-10-01 12:30:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::YEAR,
                    'is_public'                     => true,
                ],
                [
                    'title'          => str_random(16),
                    'start_datetime' => Carbon::parse('2017-07-14 11:45:00'),
                    'end_datetime'   => Carbon::parse('2017-07-14 12:45:00'),
                    'description'    => str_random(32),
                    'is_recurring'   => false,
                    'is_public'      => true,
                ],
                [
                    // 2017-08-12, 2017-08-26
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-07-15 14:45:00'),
                    'end_datetime'                  => Carbon::parse('2017-07-15 15:45:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                [
                    // 2017-08-02
                    'title'        => str_random(16),
                    'start_datetime'   => Carbon::parse('2017-08-02 9:00:00'),
                    'end_datetime'     => Carbon::parse('2017-08-02 10:00:00'),
                    'description'  => str_random(32),
                    'is_recurring' => false,
                    'is_public'    => true,
                ],
                [
                    // 2017-08-03, 2017-08-10, 2017-08-17, 2017-08-24, 2017-08-31
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-08-03 10:00:00'),
                    'end_datetime'                  => Carbon::parse('2017-08-03 14:00:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                ],
                [
                    // 2017-08-04, 2017-08-18
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-08-04 10:00:00'),
                    'end_datetime'                  => Carbon::parse('2017-08-04 14:00:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 2,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                    'end_of_recurring'              => Carbon::parse('2017-09-25'),
                ],
                [
                    // 2017-08-06
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-07-06 10:00:00'),
                    'end_datetime'                  => Carbon::parse('2017-07-06 14:00:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::MONTH,
                    'is_public'                     => true,
                ],
                [
                    'title'                         => str_random(16),
                    'start_datetime'                => Carbon::parse('2017-09-01 10:00:00'),
                    'end_datetime'                  => Carbon::parse('2017-09-01 14:00:00'),
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                    'end_of_recurring'              => Carbon::parse('2017-09-22'),
                ],
            ],
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
            $isExist = in_array($calendarEvent->start_datetime->format('Y-m-d'), $dates);
            $this->assertTrue($isExist);
        }
    }

    public function dataProvider_for_eventsOfMonth_after_merge_date_time()
    {
        for ($i = 1; $i <= 31; $i++) {
            $array[] = [$i];
        }

        return $array;
    }

    /**
     * @test
     * @dataProvider dataProvider_for_eventsOfMonth_after_merge_date_time
     * @param int $day
     */
    public function getEventsOfMonth_after_merge_date_time(int $day)
    {
        $startDateTime = Carbon::parse('2019-07-' . $day . ' 18:00:00');
        $this->calendarEvent->createCalendarEvent([
            'title'                         => str_random(16),
            'start_datetime'                => $startDateTime,
            'end_datetime'                  => clone($startDateTime)->addHours(2),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
            'is_public'                     => true,
            'end_of_recurring'              => null,
        ]);

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-07'));
        $this->assertEquals(32 - $day, $calendarEvents->count());
    }

    /**
     * @test
     * @dataProvider dataProvider_for_eventsOfMonth_after_merge_date_time
     * @param int $day
     */
    public function getEventsOfMonth_after_merge_date_time_nextMonth(int $day)
    {
        $startDateTime = Carbon::parse('2019-07-' . $day . ' 18:00:00');
        $this->calendarEvent->createCalendarEvent([
            'title'                         => str_random(16),
            'start_datetime'                => $startDateTime,
            'end_datetime'                  => (clone($startDateTime))->addHours(2),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
            'is_public'                     => true,
            'end_of_recurring'              => null,
        ]);

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-08'));
        $this->assertEquals(31, $calendarEvents->count());
    }

    /**
     * @test
     */
    public function getEventsOfMonth_after_merge_date_time_Type_week()
    {
        $startDateTime = Carbon::parse('2019-07-03 18:00:00');
        $this->calendarEvent->createCalendarEvent([
            'title'                         => str_random(16),
            'start_datetime'                => $startDateTime,
            'end_datetime'                  => (clone($startDateTime))->addHours(2),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
            'end_of_recurring'              => null,
        ]);

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-07'));
        $this->assertEquals(5, $calendarEvents->count());
    }

    /**
     * @test
     */
    public function getEventsOfMonth_after_merge_date_time_Type_month()
    {
        $startDateTime = Carbon::parse('2019-07-18 20:45:00');
        $this->calendarEvent->createCalendarEvent([
            'title'                         => str_random(16),
            'start_datetime'                => $startDateTime,
            'end_datetime'                  => (clone($startDateTime))->addHours(2),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::MONTH,
            'is_public'                     => true,
            'end_of_recurring'              => null,
        ]);

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-07'));
        $this->assertEquals(1, $calendarEvents->count());
        $this->assertEquals('2019-07-18 20:45:00', $calendarEvents->first()->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     */
    public function getEventsOfMonth_after_merge_date_time_Type_year()
    {
        $startDateTime = Carbon::parse('2019-07-18 20:00:00');
        $this->calendarEvent->createCalendarEvent([
            'title'                         => str_random(16),
            'start_datetime'                => $startDateTime,
            'end_datetime'                  => (clone($startDateTime))->addHours(2),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::YEAR,
            'is_public'                     => true,
            'end_of_recurring'              => null,
        ]);

        $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-07'));
        $this->assertEquals(1, $calendarEvents->count());
        $this->assertEquals('2019-07-18 20:00:00', $calendarEvents->first()->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
    * @test
    */
    public function getEventsOfMonth_for_nthweekRecurring()
    {
      $this->calendarEvent->createCalendarEvent([
          'start_datetime'                => '2019-06-19 19:30:00',
          'end_datetime'                  => '2019-06-19 20:00:00',
          'description'                   => str_random(32),
          'is_recurring'                  => true,
          'frequence_number_of_recurring' => 1,
          'frequence_type_of_recurring'   => RecurringFrequenceType::NTHWEEKDAY,
          'is_public'                     => true,
          'end_of_recurring'              => null,
      ]);


      $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-07'));
      $this->assertEquals(1, $calendarEvents->count());
      $this->assertEquals('2019-07-17', $calendarEvents->first()->start_datetime->format('Y-m-d'));

      $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-08'));
      $this->assertEquals(1, $calendarEvents->count());
      $this->assertEquals('2019-08-21', $calendarEvents->first()->start_datetime->format('Y-m-d'));

      $calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2019-09'));
      $this->assertEquals(1, $calendarEvents->count());
      $this->assertEquals('2019-09-18', $calendarEvents->first()->start_datetime->format('Y-m-d'));
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
        $inputCreate   = [
            'title'          => str_random(16),
            'start_datetime' => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'   => Carbon::parse('2017-08-25 17:00:00'),
            'description'    => str_random(32),
            'is_recurring'   => false,
            'is_public'      => true,
        ];
        $calendarEvent = $this->calendarEvent->createCalendarEvent($inputCreate);

        $calendarEvent->deleteCalendarEvent(true);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_generatedEvent()
    {
        $inputCreate       = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $calendarEvent     = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext = $calendarEvent->template->generateNextCalendarEvent(Carbon::parse('2017-08-27'));

        $calendarEvent->deleteCalendarEvent(true);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->fresh()->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEvent->start_datetime->format('Y-m-d'), $calendarEvent->template->end_of_recurring->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_notRecurring()
    {
        $inputCreate   = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $calendarEvent = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEvent->deleteCalendarEvent(false);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNull($calendarEvent->template->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_deleted()
    {
        $inputCreate   = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $calendarEvent = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEvent->deleteCalendarEvent(true);

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEvent->start_datetime->format('Y-m-d'), $calendarEvent->template->end_of_recurring->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_deleted_withoutInput()
    {
        $inputCreate   = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $calendarEvent = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEvent->deleteCalendarEvent();

        $this->assertNotNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEvent->start_datetime->format('Y-m-d'), $calendarEvent->template->end_of_recurring->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_recurring_recurring_notDeleted()
    {
        $inputCreate       = [
            'title'                         => str_random(16),
            'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
            'end_datetime'                  => Carbon::parse('2017-08-25 17:00:00'),
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $calendarEvent     = $this->calendarEvent->createCalendarEvent($inputCreate);
        $calendarEventNext = $calendarEvent->template->generateNextCalendarEvent(Carbon::now());
        $calendarEventNext->deleteCalendarEvent(true);

        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNotNull($calendarEventNext->deleted_at);
        $this->assertNull($calendarEvent->template->deleted_at);
        $this->assertEquals($calendarEventNext->start_datetime->format('Y-m-d'), $calendarEvent->template->end_of_recurring->format('Y-m-d'));
    }
}

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
            'title',
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
     * @test
     */
    public function user_null()
    {
        $this->app['config']->set('calendar-event.user.model', null);
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create();
        $user                  = $templateCalendarEvent->user();

        $this->assertNull($user);
    }

    public function user()
    {
        $this->app['config']->set('calendar-event.user.model', null);
//        $this->assertInstanceOf('App\Models\User', $templateCalendarEvent->user);
    }

    /**
     * @test
     */
    public function place_null()
    {
        $this->app['config']->set('calendar-event.place.model', null);
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create();
        $place                 = $templateCalendarEvent->place();

        $this->assertNull($place);
    }

    /**
     * @test
     */
    public function scopeRecurring()
    {
        factory(TemplateCalendarEvent::class)->create([
            'start_date'   => '2017-08-29',
            'start_time'   => Carbon::now()->hour,
            'end_time'     => Carbon::now()->addHour()->hour,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ]);
        factory(TemplateCalendarEvent::class)->create([
            'start_date'                    => '2017-08-29',
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ]);

        $templateCalendarEvents = $this->templateCalendarEvent->recurring()->get();

        $this->assertEquals(1, $templateCalendarEvents->count());
        $this->assertEquals(true, $templateCalendarEvents->first()->is_recurring);
    }

    /**
     * @test
     */
    public function scopePublic()
    {
        $input = [
            'start_date'   => '2017-08-29',
            'start_time'   => Carbon::now()->hour,
            'end_time'     => Carbon::now()->addHour()->hour,
            'description'  => str_random(32),
            'is_recurring' => false,
            'is_public'    => true,
        ];

        factory(TemplateCalendarEvent::class)->create(array_merge($input, ['is_public' => false]));
        factory(TemplateCalendarEvent::class)->create($input);
        factory(TemplateCalendarEvent::class)->create($input);

        $this->assertEquals(2, $this->templateCalendarEvent->public()->count());
    }

    public function dataProvider_for_getNextCalendarEventStartDate()
    {
        return [
            [
                [
                    'start_date'   => '2017-08-30',
                    'start_time'   => Carbon::now()->hour,
                    'end_time'     => Carbon::now()->addHour()->hour,
                    'description'  => 'Foo bar',
                    'is_recurring' => false,
                ],
                null
            ],
            [
                [
                    'start_date'                    => '2017-08-30',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => 'Foo bar',
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::DAY,
                    'is_public'                     => true,
                ],
                Carbon::parse('2017-08-30')->addDay()
            ],
            [
                [
                    'start_date'                    => '2017-08-30',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => 'Foo bar',
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                    'end_of_recurring'              => Carbon::parse('2017-08-30'),
                ],
                null
            ],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_getNextCalendarEventStartDate
     * @param $input
     * @param $result
     */
    public function getNextCalendarEventStartDate($input, $result)
    {
        $calendarEvent = $this->calendarEvent->createCalendarEvent($input);
        $startDate     = $calendarEvent->template->getNextCalendarEventStartDate(Carbon::parse($input['start_date']));

        $this->assertEquals($result, $startDate);
    }

    /**
     * Data provider for generate next calendar event
     * @return array
     */
    public function dataProvider_for_generateNextCalendarEvent()
    {
        return [
            ['2017-08-10', 4, RecurringFrequenceType::DAY, '2017-08-14'],
            ['2017-08-10', 2, RecurringFrequenceType::WEEK, '2017-08-24'],
            ['2017-08-10', 3, RecurringFrequenceType::MONTH, '2017-11-10'],
            ['2017-08-10', 1, RecurringFrequenceType::YEAR, '2018-08-10'],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_generateNextCalendarEvent
     * @param $startDate
     * @param $frequence_number_of_recurring
     * @param $frequence_type_of_recurring
     * @param $calendarEventNext_startDate
     */
    public function generateNextCalendarEvent($startDate, $frequence_number_of_recurring, $frequence_type_of_recurring, $calendarEventNext_startDate)
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create([
            'start_date'                    => $startDate,
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => $frequence_number_of_recurring,
            'frequence_type_of_recurring'   => $frequence_type_of_recurring,
            'is_public'                     => true,
        ]);
        $calendarEvent         = factory(CalendarEvent::class)->make(['start_date' => $startDate]);
        $calendarEvent->template()->associate($templateCalendarEvent);
        $calendarEvent->save();

        $calendarEventNext = $templateCalendarEvent->generateNextCalendarEvent(Carbon::parse($startDate));

        $this->assertInstanceOf(CalendarEvent::class, $calendarEventNext);
        $this->assertInstanceOf(TemplateCalendarEvent::class, $calendarEventNext->template);
        $this->assertEquals($calendarEvent->template->id, $calendarEventNext->template->id);
        $this->assertEquals($calendarEventNext_startDate, $calendarEventNext->start_date->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function createCalendarEvent()
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create();
        $startDate             = Carbon::parse('2017-08-29');
        $calendarEvent         = $templateCalendarEvent->createCalendarEvent($startDate);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertInstanceOf(CalendarEvent::class, $templateCalendarEvent->events->first());
        $this->assertEquals($templateCalendarEvent->id, $calendarEvent->template_calendar_event_id);
        $this->assertEquals($startDate, $calendarEvent->start_date);
    }

    /**
     * @test
     */
    public function editCalendarEvent_notExistCalendarEvent()
    {
        $input                 = [
            'start_date'                    => '2017-08-30',
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => 'Foo bar',
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create($input);
        $calendarEvent         = $templateCalendarEvent->createCalendarEvent(Carbon::parse($input['start_date']));
        $startDateNext         = $calendarEvent->start_date->addWeek();
        $calendarEventUpdated  = $templateCalendarEvent->editCalendarEvent($startDateNext, [
            'description' => 'Lorem ipsum',
        ]);

        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertEquals($templateCalendarEvent->id, $calendarEventUpdated->template->parent_id);
        $this->assertEquals($templateCalendarEvent->fresh()->end_of_recurring, $startDateNext);
    }

    /**
     * @test
     */
    public function editCalendarEvent_existCalendarEvent()
    {
        $input                 = [
            'start_date'                    => '2017-08-30',
            'start_time'                    => Carbon::now()->hour,
            'end_time'                      => Carbon::now()->addHour()->hour,
            'description'                   => 'Foo bar',
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true,
        ];
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create($input);
        $calendarEvent         = $templateCalendarEvent->createCalendarEvent(Carbon::parse($input['start_date']));
        $calendarEventUpdated  = $templateCalendarEvent->editCalendarEvent($calendarEvent->start_date, [
            'description' => 'Lorem ipsum',
        ]);

        $this->assertNotNull($calendarEvent->fresh()->deleted_at);
        $this->assertInstanceOf(CalendarEvent::class, $calendarEvent);
        $this->assertEquals($templateCalendarEvent->id, $calendarEventUpdated->template->parent_id);
        $this->assertEquals($templateCalendarEvent->fresh()->end_of_recurring, $calendarEvent->start_date);
    }

    /**
     * Data provider for generateNextCalendarEvent_notGenerated
     * @return array
     */
    public function dataProvider_for_generateNextCalendarEvent_notGenerated()
    {
        return [
            [
                [
                    'start_date'                    => '2017-08-29',
                    'start_time'                    => Carbon::now()->hour,
                    'end_time'                      => Carbon::now()->addHour()->hour,
                    'description'                   => str_random(32),
                    'is_recurring'                  => true,
                    'frequence_number_of_recurring' => 1,
                    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
                    'is_public'                     => true,
                    'end_of_recurring'              => '2017-09-04',
                ]
            ],
            [
                [
                    'start_date'   => '2017-08-29',
                    'start_time'   => Carbon::now()->hour,
                    'end_time'     => Carbon::now()->addHour()->hour,
                    'description'  => str_random(32),
                    'is_recurring' => false,
                    'is_public'    => true,
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_for_generateNextCalendarEvent_notGenerated
     */
    public function generateNextCalendarEvent_notGenerated(array $input)
    {
        $templateCalendarEvent = factory(TemplateCalendarEvent::class)->create($input);
        $calendarEvent         = factory(CalendarEvent::class)->make(['start_date' => $input['start_date']]);
        $calendarEvent->template()->associate($templateCalendarEvent);
        $calendarEvent->save();

        $calendarEventNext = $templateCalendarEvent->generateNextCalendarEvent(Carbon::parse($input['start_date']));

        $this->assertNull($calendarEventNext);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_notExist()
    {
        $calendarEvent         = $this->calendarEvent->createCalendarEvent([
            'start_date'                    => '2017-08-25',
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $templateCalendarEvent = $calendarEvent->template;

        $startDate = Carbon::parse('2017-09-08');
        $isDeleted = $templateCalendarEvent->deleteCalendarEvent($startDate);

        $this->assertTrue($isDeleted);
        $this->assertNull($calendarEvent->deleted_at);
        $this->assertNull($templateCalendarEvent->deleted_at);
        $this->assertEquals($startDate, $templateCalendarEvent->end_of_recurring);
    }

    /**
     * @test
     */
    public function deleteCalendarEvent_exist()
    {
        $startDate             = Carbon::parse('2017-08-25');
        $calendarEvent         = $this->calendarEvent->createCalendarEvent([
            'start_date'                    => $startDate,
            'start_time'                    => 16,
            'end_time'                      => 17,
            'description'                   => str_random(32),
            'is_recurring'                  => true,
            'frequence_number_of_recurring' => 1,
            'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
            'is_public'                     => true
        ]);
        $templateCalendarEvent = $calendarEvent->template;
        $isDeleted             = $templateCalendarEvent->deleteCalendarEvent($startDate);

        $this->assertTrue($isDeleted);
        $this->assertNotNull($calendarEvent->fresh()->deleted_at);
        $this->assertNotNull($templateCalendarEvent->fresh()->deleted_at);
        $this->assertEquals($startDate, $templateCalendarEvent->fresh()->end_of_recurring);
    }
}

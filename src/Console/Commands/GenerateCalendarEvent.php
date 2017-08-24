<?php

namespace T1k3\LaravelCalendarEvent\Console\Commands;


use Illuminate\Console\Command;
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

/**
 * Class GenerateCalendarEvent
 * @package T1k3\LaravelCalendarEvent\Console\Commands
 */
class GenerateCalendarEvent extends Command
{
    /**
     * Console command
     * @var string
     */
    protected $signature = 'generate:calendar-event';

    /**
     * Console command description
     * @var string
     */
    protected $description = 'Generate CalendarEvent to (recurring) TemplateCalendarEvent';

    /**
     * TemplateCalendarEvent DAO
     * @var TemplateCalendarEvent
     */
    private $templateCalendarEventDao;

    /**
     * CalendarEvent DAO
     * @var CalendarEvent
     */
    private $calendarEventDao;

    /**
     * GenerateCalendarEvent constructor.
     * @param TemplateCalendarEvent $templateCalendarEventDao
     * @param CalendarEvent $calendarEventDao
     */
    public function __construct(TemplateCalendarEvent $templateCalendarEventDao, CalendarEvent $calendarEventDao)
    {
        parent::__construct();

        $this->templateCalendarEventDao = $templateCalendarEventDao;
        $this->calendarEventDao         = $calendarEventDao;
    }

    public function handle()
    {
//        TODO Fill me
    }
}
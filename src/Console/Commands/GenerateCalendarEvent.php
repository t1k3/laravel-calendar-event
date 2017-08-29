<?php

namespace T1k3\LaravelCalendarEvent\Console\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
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
    protected $signature = 'generate:calendar-event {--date=}';

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
     * GenerateCalendarEvent constructor.
     * @param TemplateCalendarEvent $templateCalendarEventDao
     */
    public function __construct(TemplateCalendarEvent $templateCalendarEventDao)
    {
        parent::__construct();
        $this->templateCalendarEventDao = $templateCalendarEventDao;
    }

    /**
     * Handle
     */
    public function handle()
    {
        $date                   = $this->option('date') ? $this->option('date') : date('Y-m-d');
        $templateCalendarEvents = $this->templateCalendarEventDao
            ->where('is_recurring', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_of_recurring')
                    ->orWhere('end_of_recurring', '>=', $date);
            })
            ->get();

        $now = Carbon::parse($date);
        foreach ($templateCalendarEvents as $templateCalendarEvent) {
            $templateCalendarEvent->generateNextCalendarEvent($now);
        }

        $this->info('Generated: next calendar events.');
    }
}
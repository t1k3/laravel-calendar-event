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
     * TemplateCalendarEvent
     * @var TemplateCalendarEvent
     */
    private $templateCalendarEvent;

    /**
     * GenerateCalendarEvent constructor.
     * @param TemplateCalendarEvent $templateCalendarEvent
     */
    public function __construct(TemplateCalendarEvent $templateCalendarEvent)
    {
        parent::__construct();
        $this->templateCalendarEvent = $templateCalendarEvent;
    }

    /**
     * Handle
     */
    public function handle()
    {
        $date                   = $this->option('date') ? $this->option('date') : date('Y-m-d');
        $templateCalendarEvents = $this->templateCalendarEvent
            ->where('is_recurring', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_of_recurring')
                    ->orWhere('end_of_recurring', '>=', $date);
            })
            ->get();
        
        foreach ($templateCalendarEvents as $templateCalendarEvent) {
            $templateCalendarEvent->generateNextCalendarEvent(Carbon::parse($date));
        }

        $this->info('Generated: next calendar events.');
    }
}
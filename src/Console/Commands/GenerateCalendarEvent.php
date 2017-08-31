<?php

namespace T1k3\LaravelCalendarEvent\Console\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
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
        $date                   = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $templateCalendarEvents = $this->templateCalendarEvent
            ->where('is_recurring', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_of_recurring')
                    ->orWhere('end_of_recurring', '>=', $date);
            })
            ->get();

        $count = 0;
        foreach ($templateCalendarEvents as $templateCalendarEvent) {
            $dateNext       = null;
            $endOfRecurring = (clone $date)->addWeek();
//          TODO Refactor | OCP, Strategy
            switch ($templateCalendarEvent->frequence_type_of_recurring) {
                case RecurringFrequenceType::DAY:
                    $endOfRecurring = (clone $date);
                    $diff           = $templateCalendarEvent->start_date->diffInDays($endOfRecurring);
                    $dateNext       = $templateCalendarEvent->start_date->addDays($diff);

                    if ($templateCalendarEvent->start_date->diffInDays($dateNext) % (int)$templateCalendarEvent->frequence_number_of_recurring === 0) {
                        $dateNext->addDays((int)$templateCalendarEvent->frequence_number_of_recurring - 1);
                        $endOfRecurring->addDays((int)$templateCalendarEvent->frequence_number_of_recurring - 1);
                    }
                    break;
                case RecurringFrequenceType::WEEK:
//                    $endOfRecurring = (clone $date)->addWeek();
                    $diff     = $templateCalendarEvent->start_date->diffInWeeks($endOfRecurring);
                    $dateNext = $templateCalendarEvent->start_date->addWeeks($diff);
                    break;
                case RecurringFrequenceType::MONTH:
//                    $endOfRecurring = (clone $date)->addMonth();
                    $diff     = $templateCalendarEvent->start_date->diffInMonths($endOfRecurring);
                    $dateNext = $templateCalendarEvent->start_date->addMonths($diff);
                    break;
                case RecurringFrequenceType::YEAR:
//                    $endOfRecurring = (clone $date)->addYear();
                    $diff     = $templateCalendarEvent->start_date->diffInYears($endOfRecurring);
                    $dateNext = $templateCalendarEvent->start_date->addYears($diff);
            }

            if ($dateNext !== null
                && ($templateCalendarEvent->end_of_recurring === null
                    || $dateNext <= $templateCalendarEvent->end_of_recurring
                )
                && $dateNext >= $date
                && $dateNext <= $endOfRecurring
                && !$templateCalendarEvent->events()->withTrashed()->where('start_date', $dateNext)->first()
            ) {
                $templateCalendarEvent->createCalendarEvent($dateNext);
                $count++;
            }
        }

        $this->info(sprintf('Generated: next calendar events: %s.', $count));
    }
}
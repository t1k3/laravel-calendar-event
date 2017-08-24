<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class CalendarEvent
 * @package T1k3\LaravelCalendarEvent\Models
 */
class CalendarEvent extends AbstractModel
{
    use SoftDeletes;

    /**
     * Fillable
     * @var array
     */
    protected $fillable = [
        'start_date'
    ];

    /**
     * TemplateCalendarEvent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(TemplateCalendarEvent::class, 'template_calendar_event_id');
    }

    /**
     * CalendarEvent && TemplateCalendarEvent is equal with this data ($values)
     * @param array $values
     * @return bool
     */
    public function isEqual(array $values): bool
    {
        $templateAttributes = $this->template
            ->getAttributesArray([
                'start_date',
                'start_time',
                'end_time',
                'is_recurring',
                'is_public'
            ]);
        $templateValues     = $values;
        unset($templateValues['description']);

        if ($templateAttributes === $templateValues
            && $this->description === $values['description']
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param array $values
     * @return CalendarEvent
     */
    public function createEvent(array $values): CalendarEvent
    {
        DB::transaction(function () use ($values, &$calendarEvent) {
            $templateCalendarEvent = $this->template()->create($values);
            $calendarEvent         = $this->make(['start_date' => $templateCalendarEvent->start_date]);
            $calendarEvent->template()->associate($templateCalendarEvent);
            $calendarEvent->save();
        });

        return $calendarEvent;
    }

    /**
     * @param array $values
     * @return null|CalendarEvent
     */
    public function editEvent(array $values)
    {
        if (!$this->isEqual($values)) {
            $calendarEvent = $this->createEvent($values);
            $calendarEvent->template->parent()->associate($this->template);

            $this->template->delete();
            $this->delete();

            return $calendarEvent;
        }
        return null;
    }
}

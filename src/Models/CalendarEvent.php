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
     * Calendar event (template) data - input is different
     * @param array $attributes
     * @return bool
     */
    public function dataIsDifferent(array $attributes): bool
    {
        if (isset($attributes['start_date'])) {
            if ($this->start_date !== $attributes['start_date']) {
                return true;
            }
            unset($attributes['start_date']);
        }

        $template = $this->template;
        foreach ($attributes as $key => $value) {
            if ($template->{$key} !== $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent(array $attributes)
    {
        DB::transaction(function () use ($attributes, &$calendarEvent) {
            $templateCalendarEvent = $this->template()->create($attributes);
            $calendarEvent = $this->make($attributes);
            $calendarEvent->template()->associate($templateCalendarEvent);
            $calendarEvent->save();
        });

        return $calendarEvent;
    }

    /**
     * @param TemplateCalendarEvent $templateCalendarEvent
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generateCalendarEvent(TemplateCalendarEvent $templateCalendarEvent)
    {
//        TODO Fill me
    }

    /**
     * @param array $attributes
     * @return null|CalendarEvent
     */
    public function editCalendarEvent(array $attributes)
    {
        if ($this->dataIsDifferent($attributes)) {
            $calendarEventNew = $this->createCalendarEvent(
                array_merge(
                    $this->template->toArray(),
                    ['start_date' => $this->start_date],
                    $attributes
                )
            );

            $calendarEventNew->template->parent()->associate($this->template);
            if ($this->template->is_recurring && $calendarEventNew->template->is_recurring) {
                $this->template->update([
                    'end_of_recurring' => $this->start_date
                ]);
            }
            $this->delete();

            return $calendarEventNew;
        }
        return null;
    }

    public function scopeEventsOfMonth($query, $month)
    {
//        TODO Q: This is a scope?
//        TODO Fill me
    }
}

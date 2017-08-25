<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;

/**
 * Class TemplateCalendarEvent
 * @package T1k3\LaravelCalendarEvent\Models
 */
class TemplateCalendarEvent extends AbstractModel
{
    use SoftDeletes;

    /**
     * Fillable
     * @var array
     */
    protected $fillable = [
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

    /**
     * Attribute Casting
     * @var array
     */
    protected $casts = [
        'is_recurring'     => 'boolean',
        'is_public'        => 'boolean',
        'start_date'       => 'date',
        'end_of_recurring' => 'date',
    ];

    /**
     * TemplateCalendarEvent parent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(TemplateCalendarEvent::class, 'parent_id');
    }

    /**
     * CalendarEvent
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(CalendarEvent::class, 'template_calendar_event_id');
    }

    /**
     * @param $query
     * @return Builder
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Generate next calendar event to template
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function generateNextCalendarEvent()
    {
        if (!$this->is_recurring
            || ($this->end_of_recurring !== null
                && $this->end_of_recurring <= Carbon::now()->format('Y-m-d')
            )
        ) {
            return null;
        }

        $calendarEvent = $this->events()->orderBy('start_date', 'desc')->first();
        $startDate     = Carbon::parse($calendarEvent->start_date);
        switch ($this->frequence_type_of_recurring) {
            case RecurringFrequenceType::DAY:
                $startDate->addDays($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::WEEK:
                $startDate->addWeeks($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::MONTH:
                $startDate->addMonths($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::YEAR:
                $startDate->addYears($this->frequence_number_of_recurring);
        }

        $calendarEventNext = $this->events()->make(['start_date' => $startDate]);
        $calendarEventNext->template()->associate($this);
//        $calendarEventNext->save();

        return $calendarEventNext;
    }
}

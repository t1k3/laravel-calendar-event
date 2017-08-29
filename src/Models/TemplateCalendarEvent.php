<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Interfaces\PlaceInterface;
use T1k3\LaravelCalendarEvent\Interfaces\UserInterface;

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

    /*protected function associatePlace(PlaceInterface $place)
    {
        return $this->place()->associate($place);
    }

    protected function associateUser(UserInterface $user)
    {
        $this->user()->associate($user);
    }*/

    /**
     * Create Calendar Event for TemplateCalendarEvent
     * @param $startDate
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent($startDate)
    {
        $calendarEvent = $this->events()->make(['start_date' => $startDate]);
        $calendarEvent->template()->associate($this);
        $calendarEvent->save();

        return $calendarEvent;
    }

    /**
     * Generate next calendar event to template
     * @param \DateTimeInterface $now
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function generateNextCalendarEvent(\DateTimeInterface $now)
    {
        if (!$this->is_recurring
            || ($this->end_of_recurring !== null
                && $this->end_of_recurring <= $now)
        ) {
            return null;
        }

        $calendarEvent = $this->events()->withTrashed()->orderBy('start_date', 'desc')->first();
        $startDate     = $this->getNextCalendarEventStartDate($calendarEvent->start_date);
        if ($startDate === null) {
            return null;
        }

        return $this->createCalendarEvent($startDate);
    }

    public function getNextCalendarEventStartDate(\DateTimeInterface $startDate)
    {
        if(!$this->is_recurring) {
            return null;
        }

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

        if (($this->end_of_recurring !== null
            && $this->end_of_recurring < $startDate)
//            || $this->events()->withTrashed()->where('start_date', $startDate)->whereNotNull('deleted_at')->first()
        ) {
            return null;
        }

        return $startDate;
    }
}

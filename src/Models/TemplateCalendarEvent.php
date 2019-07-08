<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Interfaces\PlaceInterface;
use T1k3\LaravelCalendarEvent\Interfaces\TemplateCalendarEventInterface;
use T1k3\LaravelCalendarEvent\Interfaces\UserInterface;

/**
 * Class TemplateCalendarEvent
 * @package T1k3\LaravelCalendarEvent\Models
 */
class TemplateCalendarEvent extends AbstractModel implements TemplateCalendarEventInterface
{
    use SoftDeletes;

    /**
     * Fillable
     * @var array
     */
    protected $fillable = [
        'title',
        'start_datetime',
        'end_datetime',
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
        'start_datetime'   => 'datetime',
        'end_datetime'     => 'datetime',
        'end_of_recurring' => 'datetime',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function user()
    {
//        TODO If the config is null then you can not use ->user (LogicException), just ->user()
        $class = config('calendar-event.user.model');
        return $class ? $this->belongsTo($class) : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function place()
    {
//        TODO If the config is null then you can not use ->place (LogicException), just ->place()
        $class = config('calendar-event.place.model');
        return $class ? $this->belongsTo($class) : null;
    }

    /**
     * Create Calendar Event for TemplateCalendarEvent
     * @param \DateTimeInterface $startDateTime
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent(\DateTimeInterface $startDate)
    {
        $diffInSeconds = $this->start_datetime->diffInSeconds($this->end_datetime);
        $endDate    = clone($startDate);
        $endDate    = $endDate->addSeconds($diffInSeconds);
        $calendarEvent = $this->events()->make([
            'start_datetime' => $startDate,
            'end_datetime'   => Carbon::parse( $endDate->format('Y-m-d') . ' ' . $this->end_datetime->format('H:i:s') ),
        ]);
        
        $calendarEvent->template()->associate($this);
        $calendarEvent->save();

        return $calendarEvent;
    }

    /**
     * Create or get calendar event
     * @param \DateTimeInterface $startDateTime
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function createOrGetCalendarEvent(\DateTimeInterface $startDateTime)
    {
        $calendarEvent = $this->events()->where('start_datetime', $startDateTime)->first();
        if (!$calendarEvent) {
            $calendarEvent = $this->createCalendarEvent($startDateTime);
        }

        return $calendarEvent;
    }

    /**
     * Edit calendar event | Exist or not | Check data
     * @param \DateTimeInterface $startDateTime
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return null|CalendarEvent
     */
    public function editCalendarEvent(\DateTimeInterface $startDateTime, array $attributes, UserInterface $user = null, PlaceInterface $place = null)
    {
        $calendarEvent = $this->createOrGetCalendarEvent($startDateTime);
        return $calendarEvent->editCalendarEvent($attributes, $user, $place);
    }

    /**
     * Edit calendar event | Exist or not | Do not check data
     * @param \DateTimeInterface $startDateTime
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return mixed
     */
    public function updateCalendarEvent(\DateTimeInterface $startDateTime, array $attributes, UserInterface $user = null, PlaceInterface $place = null)
    {
        $calendarEvent = $this->createOrGetCalendarEvent($startDateTime);
        return $calendarEvent->updateCalendarEvent($attributes, $user, $place);
    }

    /**
     * Delete calendar event | Exist or not
     * @param \DateTimeInterface $startDateTime
     * @param bool|null $isRecurring
     * @return bool|null
     */
    public function deleteCalendarEvent(\DateTimeInterface $startDateTime, bool $isRecurring = null)
    {
        if ($isRecurring === null) $isRecurring = $this->is_recurring;

        $calendarEvent = $this->events()->where('start_datetime', $startDateTime)->first();
        if (!$calendarEvent) {
            $calendarEvent = $this->createCalendarEvent($startDateTime);
        }

        return $calendarEvent->deleteCalendarEvent($isRecurring);
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

        $calendarEvent = $this->events()->withTrashed()->orderBy('start_datetime', 'desc')->first();
        $startDateTime     = $this->getNextCalendarEventStartDateTime($calendarEvent->start_datetime);
        if ($startDateTime === null) {
            return null;
        }

        return $this->createCalendarEvent($startDateTime);
    }

    /**
     * Get next calendar event start date
     * @param \DateTimeInterface $startDateTime
     * @return \DateTimeInterface|null
     */
    public function getNextCalendarEventStartDateTime(\DateTimeInterface $startDateTime)
    {
        if (!$this->is_recurring) {
            return null;
        }

//        TODO Refactor: OCP, Strategy
        switch ($this->frequence_type_of_recurring) {
            case RecurringFrequenceType::DAY:
                $startDateTime->addDays($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::WEEK:
                $startDateTime->addWeeks($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::MONTH:
                $startDateTime->addMonths($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::YEAR:
                $startDateTime->addYears($this->frequence_number_of_recurring);
                break;
            case RecurringFrequenceType::NTHWEEKDAY:
                $nextMonth = $startDateTime->copy()->addMonths($this->frequence_number_of_recurring);
                    $weekdays = getWeekdaysInMonth(
                    $startDateTime->format('l'),
                    $nextMonth
                );
                $startDateTime = $weekdays[$startDateTime->weekOfMonth - 1];
        }

        if (($this->end_of_recurring !== null
            && $this->end_of_recurring < $startDateTime)
//            || $this->events()->withTrashed()->where('start_datetime', $startDateTime)->whereNotNull('deleted_at')->first()
        ) {
            return null;
        }

        return $startDateTime;
    }
}

<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;
use T1k3\LaravelCalendarEvent\Exceptions\InvalidMonthException;
use T1k3\LaravelCalendarEvent\Interfaces\CalendarEventInterface;
use T1k3\LaravelCalendarEvent\Interfaces\PlaceInterface;
use T1k3\LaravelCalendarEvent\Interfaces\UserInterface;

/**
 * Class CalendarEvent
 * @package T1k3\LaravelCalendarEvent\Models
 */
class CalendarEvent extends AbstractModel implements CalendarEventInterface
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
     * Attribute Casting
     * @var array
     */
    protected $casts = [
        'start_date' => 'date'
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
            // CalendarEvent data check
            if ($this->start_date->format('Y-m-d') !== $attributes['start_date']->format('Y-m-d')) {
                return true;
            }
            unset($attributes['start_date']);
        }

        // TemplateCalendarEvent data check | Skip start_date from template
        return !arrayIsEqualWithDB($attributes, $this->template, ['start_date']);
    }

    /**
     * Create CalendarEvent with Template, User, Place
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null)
    {
        DB::transaction(function () use ($attributes, $user, $place, &$calendarEvent) {
            $templateCalendarEvent = $this->template()->make($attributes);

            if ($templateCalendarEvent->user() !== null && $user !== null) $templateCalendarEvent->user()->associate($user);
            if ($templateCalendarEvent->place() !== null && $place !== null) $templateCalendarEvent->place()->associate($place);

            $templateCalendarEvent->save();

            $calendarEvent = $this->make($attributes);
            $calendarEvent->template()->associate($templateCalendarEvent);
            $calendarEvent->save();
        });

        return $calendarEvent;
    }

    /**
     * Update CalendarEvent
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return mixed
     */
    public function updateCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null)
    {
        DB::transaction(function () use ($attributes, $user, $place, &$calendarEventNew) {
            $calendarEventNew = $this->createCalendarEvent(
                array_merge(
                    $this->template->toArray(),
                    ['start_date' => $this->start_date],
                    $attributes
                ),
                $user,
                $place
            );

            $templateCalendarEvent = $calendarEventNew->template->parent()->associate($this->template);
            $templateCalendarEvent->save();

            if ($this->template->is_recurring && $calendarEventNew->template->is_recurring) {
                $this->template->update([
                    'end_of_recurring' => $this->start_date
                ]);
            } else {
                $calendarEventNew->template->update([
                    'end_of_recurring' => null
                ]);
            }
//            $this->delete();
            $this->deleteCalendarEvent($this->template->is_recurring && $templateCalendarEvent->is_recurring);
        });

        return $calendarEventNew;
    }

    /**
     * Edit\Update calendar event with data check
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return null|CalendarEvent
     */
    public function editCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null)
    {
        if ($this->dataIsDifferent($attributes)
            || ($user ? $user->id : null) != $this->template->user_id
            || ($place ? $place->id : null) != $this->template->place_id
        ) {
            return $this->updateCalendarEvent($attributes, $user, $place);
        }
        return null;
    }

    /**
     * Delete calendar event
     * @param bool|null $isRecurring
     * @return bool|null
     */
    public function deleteCalendarEvent(bool $isRecurring = null)
    {
        DB::transaction(function () use ($isRecurring, &$isDeleted) {
            if ($isRecurring === null) {
                $isRecurring = $this->template->is_recurring;
            }

            if ($this->template->is_recurring && $isRecurring) {
                $this->template->update(['end_of_recurring' => $this->start_date]);

                $nextCalendarEvents = $this->template->events()->where('start_date', '>', $this->start_date)->get();
                foreach ($nextCalendarEvents as $nextCalendarEvent) {
                    $nextCalendarEvent->delete();
                }

                if ($this->template->start_date == $this->start_date) {
                    $this->template->delete();
                }
            }

            if (!$this->template->is_recurring) {
                $this->template->delete();
            }

            $isDeleted = $this->delete();
        });
        return $isDeleted;
    }

    /**
     * Show (potential) CalendarEvent of the month
     * @param \DateTimeInterface $date
     * @return \Illuminate\Support\Collection|static
     */
    public static function showPotentialCalendarEventsOfMonth(\DateTimeInterface $date)
    {
        $endOfRecurring = $date->lastOfMonth();
        $month          = str_pad($endOfRecurring->month, 2, '0', STR_PAD_LEFT);

        $templateCalendarEvents = TemplateCalendarEvent
            ::where(function ($q) use ($month) {
                $q->where('is_recurring', false)
                    ->whereMonth('start_date', $month);
            })
            ->orWhere(function ($q) use ($endOfRecurring) {
                $q->where('is_recurring', true)
                    ->whereNull('end_of_recurring')
                    ->where('start_date', '<=', $endOfRecurring);
            })
            ->orWhere(function ($q) use ($endOfRecurring) {
                $q->where('is_recurring', true)
                    ->where('start_date', '<=', $endOfRecurring)
                    ->whereMonth('end_of_recurring', '<=', $endOfRecurring);
            })
            ->with('events')
            ->get();

        $calendarEvents = collect();
        foreach ($templateCalendarEvents as $templateCalendarEvent) {
            $calendarEvents       = $calendarEvents->merge($templateCalendarEvent->events()->whereMonth('start_date', $month)->get());
            $dateNext             = null;
            $calendarEventTmpLast = $templateCalendarEvent->events()->orderBy('start_date', 'desc')->first();

            if ($calendarEventTmpLast) {
//                TODO Refactor | OCP, Strategy
                switch ($templateCalendarEvent->frequence_type_of_recurring) {
                    case RecurringFrequenceType::DAY:
                        $diff     = $date->firstOfMonth()->diffInDays($calendarEventTmpLast->start_date);
                        $dateNext = $calendarEventTmpLast->start_date->addDays($diff);
                        break;
                    case RecurringFrequenceType::WEEK:
                        $diff     = $date->firstOfMonth()->diffInWeeks($calendarEventTmpLast->start_date);
                        $dateNext = $calendarEventTmpLast->start_date->addWeeks($diff);
                        break;
                    case RecurringFrequenceType::MONTH:
                        $diff     = $date->firstOfMonth()->diffInMonths($calendarEventTmpLast->start_date);
                        $dateNext = $calendarEventTmpLast->start_date->addMonths($diff);
                        break;
                    case RecurringFrequenceType::YEAR:
                        $diff     = $date->firstOfMonth()->diffInYears($calendarEventTmpLast->start_date);
                        $dateNext = $calendarEventTmpLast->start_date->addYears($diff);
                }
            }

            while ($dateNext !== null && $dateNext->month <= (int)$month) {
                $calendarEventNotExists                = (new CalendarEvent())->make(['start_date' => $dateNext]);
                $calendarEventNotExists->is_not_exists = true;
                $calendarEventNotExists->template()->associate($templateCalendarEvent);

                if ($calendarEventNotExists->start_date->month === (int)$month
                    && !$templateCalendarEvent->events()->where('start_date', $dateNext)->first()
                ) {
                    $calendarEvents = $calendarEvents->merge(collect([$calendarEventNotExists]));
                }

                $dateNext = $templateCalendarEvent->getNextCalendarEventStartDate($calendarEventNotExists->start_date);
            }
        }

        return $calendarEvents;
    }
}

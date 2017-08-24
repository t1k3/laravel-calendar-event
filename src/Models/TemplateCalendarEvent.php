<?php

namespace T1k3\LaravelCalendarEvent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

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
        'is_recurring' => 'boolean',
        'is_public'    => 'boolean',
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
     * @param $query
     * @return Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('end_of_recurring', '>', date('Y-m-d'));
    }
}

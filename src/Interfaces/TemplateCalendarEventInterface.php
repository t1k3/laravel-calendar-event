<?php

namespace T1k3\LaravelCalendarEvent\Interfaces;


interface TemplateCalendarEventInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events();

    /**
     * @param $query
     * @return Builder
     */
    public function scopeRecurring($query);

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function user();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function place();

    /**
     * @param \DateTimeInterface $startDate
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent(\DateTimeInterface $startDate);

    /**
     * @param \DateTimeInterface $startDate
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function createOrGetCalendarEvent(\DateTimeInterface $startDate);

    /**
     * @param \DateTimeInterface $startDate
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return null|CalendarEvent
     */
    public function editCalendarEvent(\DateTimeInterface $startDate, array $attributes, UserInterface $user = null, PlaceInterface $place = null);

    /**
     * @param \DateTimeInterface $startDate
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return mixed
     */
    public function updateCalendarEvent(\DateTimeInterface $startDate, array $attributes, UserInterface $user = null, PlaceInterface $place = null);

    /**
     * @param \DateTimeInterface $startDate
     * @param bool|null $isRecurring
     * @return bool|null
     */
    public function deleteCalendarEvent(\DateTimeInterface $startDate, bool $isRecurring = null);

    /**
     * @param \DateTimeInterface $now
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function generateNextCalendarEvent(\DateTimeInterface $now);

    /**
     * @param \DateTimeInterface $startDate
     * @return \DateTimeInterface|null
     */
    public function getNextCalendarEventStartDate(\DateTimeInterface $startDate);
}
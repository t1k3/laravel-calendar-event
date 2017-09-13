<?php

namespace T1k3\LaravelCalendarEvent\Interfaces;


interface CalendarEventInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template();

    /**
     * @param array $attributes
     * @return bool
     */
    public function dataIsDifferent(array $attributes): bool;

    /**
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null);

    /**
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return mixed
     */
    public function updateCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null);

    /**
     * @param array $attributes
     * @param UserInterface|null $user
     * @param PlaceInterface|null $place
     * @return null|CalendarEvent
     */
    public function editCalendarEvent(array $attributes, UserInterface $user = null, PlaceInterface $place = null);

    /**
     * @param bool|null $isRecurring
     * @return bool|null
     */
    public function deleteCalendarEvent(bool $isRecurring = null);

    /**
     * @param \DateTimeInterface $date
     * @return \Illuminate\Support\Collection|static
     */
    public static function showPotentialCalendarEventsOfMonth(\DateTimeInterface $date);
}
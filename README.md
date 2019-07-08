# Laravel Calendar Event

[![Latest Stable Version](https://poser.pugx.org/t1k3/laravel-calendar-event/v/stable)](https://packagist.org/packages/t1k3/laravel-calendar-event)
[![Total Downloads](https://poser.pugx.org/t1k3/laravel-calendar-event/downloads)](https://packagist.org/packages/t1k3/laravel-calendar-event)
[![License](https://poser.pugx.org/t1k3/laravel-calendar-event/license)](https://packagist.org/packages/t1k3/laravel-calendar-event)
[![Build Status](https://travis-ci.org/t1k3/laravel-calendar-event.svg?branch=master)](https://travis-ci.org/t1k3/laravel-calendar-event)
[![codecov](https://codecov.io/gh/t1k3/laravel-calendar-event/branch/master/graph/badge.svg)](https://codecov.io/gh/t1k3/laravel-calendar-event) 
[![Maintainability](https://api.codeclimate.com/v1/badges/c226ae53c829f256ec58/maintainability)](https://codeclimate.com/github/t1k3/laravel-calendar-event/maintainability)

## Installation
```bash
composer require t1k3/laravel-calendar-event
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`
```php
T1k3\LaravelCalendarEvent\ServiceProvider::class,
```

You need publish to the config.
```bash
php artisan vendor:publish --provider="T1k3\LaravelCalendarEvent\ServiceProvider"
```

You need to run the migrations for this package.
```bash
php artisan migrate
```

## Usage

#### Recurring options
- DAY
- WEEK
- MONTH
- YEAR
- NTHWEEKDAY: nth weekday per month, example 2nd Monday

#### Create CalendarEvent
If you like to attach `User` and/or `Place` then must have:
* configurate `config/calendar-event.php` 
* implements `UserInterface`, `PlaceInterface` on your Models
* you can use `CalendarEventUserTrait`, `CalendarEventPlaceTrait` in Models

```php
use T1k3\LaravelCalendarEvent\Interfaces\PlaceInterface;
use T1k3\LaravelCalendarEvent\Traits\CalendarEventPlaceTrait;

class Place extends Model implements PlaceInterface
{
    use CalendarEventPlaceTrait;
}
```

```php
use T1k3\LaravelCalendarEvent\Models\CalendarEvent;
use T1k3\LaravelCalendarEvent\Enums\RecurringFrequenceType;

$calendarEvent = new CalendarEvent();
$calendarEvent = $calendarEvent->createCalendarEvent([
    'title'                         => 'Lorem ipsum',
    'start_datetime'                => Carbon::parse('2017-08-25 16:00:00'),
    'end_datetime'                  => Carbon::parse('2017-08-25 17:30:00'),
    'description'                   => 'Lorem ipsum dolor sit amet',
    'is_recurring'                  => true,
    'frequence_number_of_recurring' => 1,
    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
    'is_public'                     => true,
    'end_of_recurring'              => Carbon::parse('2017-09-08')
], $user = null, $place = null);
```

#### Edit and Update CalendarEvent
```php
$calendarEvent        = CalendarEvent::find($id);
$calendarEventUpdated = $calendarEvent->editCalendarEvent([
    'start_datetime' => Carbon::parse('2017-08-26'),
    'is_recurring'   => false,
], $user = null, $place = null);

// $calendarEventUpdated === null ? dd('NOT_MODIFIED') : dd('MODIFIED', $calendarEventUpdated);
```

#### Update CalendarEvent (without data check)
```php
$calendarEvent        = CalendarEvent::find($id);
$calendarEventUpdated = $calendarEvent->updateCalendarEvent([
    'start_datetime' => Carbon::parse('2017-08-26'),
    'is_recurring'   => false,
], $user = null, $place = null);
```

#### Delete CalendarEvent
```php
$calendarEvent = CalendarEvent::find($id);
$isDeleted     = $calendarEvent->deleteCalendarEvent($isRecurring = null);
```

#### Edit and Update not existing CalendarEvent
```php
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

$templateCalendarEvent = TemplateCalendarEvent::find($id);
$calendarEventUpdated  = $templateCalendarEvent->editCalendarEvent(Carbon::parse('2017-08-30'), [
    'description' => 'Foo Bar'
], $user = null, $place = null);

// $calendarEventUpdated === null ? dd('NOT_MODIFIED') : dd('MODIFIED', $calendarEventUpdated);
```

#### Update not existing CalendarEvent (without data check)
```php
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

$templateCalendarEvent = TemplateCalendarEvent::find($id);
$calendarEventUpdated  = $templateCalendarEvent->updateCalendarEvent(Carbon::parse('2017-08-30'), [
    'description' => 'Foo Bar'
], $user = null, $place = null);
```

#### Delete not existing CalendarEvent
```php
$templateCalendarEvent = TemplateCalendarEvent::find($id);
$isDeleted             = $templateCalendarEvent->deleteCalendarEvent(Carbon::parse('2017-08-30'), $isRecurring = null);
```

#### Get (potential) CalendarEvent(s) of month
If the CalendarEvent is not exist then it is append `is_not_exists` attribute with `true` value
```php
$calendarEvents = CalendarEvent::showPotentialCalendarEventsOfMonth(Carbon::parse('2017-08'));
```

#### Generate next CalendarEvent(s) from Console
Do NOT forget the [Laravel Task Scheduling](https://laravel.com/docs/master/scheduling)
- The command run at hourly in schedule
```bash
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
# OR manually 
php artisan generate:calendar-event
```

### Validation
Do NOT forget the [validation](https://laravel.com/docs/master/validation) 
start_datetime - end_datetime

### TODO
- [ ] OCP
- [ ] Name conventions, example: `TemplateCalendarEvent::events()` to `TemplateCalendarEvent::calendarEvents()`
- [ ] [Custom validation rule](https://laravel.com/docs/master/validation#custom-validation-rules) to date/time diff

### Special thanks
- [Bit and Pixel](https://bitandpixel.hu)

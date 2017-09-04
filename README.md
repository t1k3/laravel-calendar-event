# Laravel Calendar Event

## Installation
```bash
composer require t1k3/laravel-calendar-event
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`
```php
T1k3\LaravelCalendarEvent\ServiceProvider::class,
```

You need to run the migrations for this package.
```bash
php artisan vendor:publish --provider="T1k3\LaravelCalendarEvent\ServiceProvider"
php artisan migrate
```

## Usage

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
    'start_date'                    => Carbon::parse('2017-08-25'),
    'start_time'                    => Carbon::parse('16:00'),
    'end_time'                      => Carbon::parse('17:30'),
    'description'                   => 'Lorem ipsum dolor sit amet',
    'is_recurring'                  => true,
    'frequence_number_of_recurring' => 1,
    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
    'is_public'                     => true,
    'end_of_recurring'              => Carbon::parse('2017-09-08')
], $user = null, $place = null);
```

#### Edit CalendarEvent
```php
$calendarEvent        = CalendarEvent::find($id);
$calendarEventUpdated = $calendarEvent->editCalendarEvent([
    'start_date'   => Carbon::parse('2017-08-26'),
    'is_recurring' => false,
], $user = null, $place = null);
```

#### Delete CalendarEvent
```php
$calendarEvent = CalendarEvent::find($id);
$isDeleted     = $calendarEvent->deleteCalendarEvent($isRecurring = null);
```

#### Edit not existing CalendarEvent
```php
use T1k3\LaravelCalendarEvent\Models\TemplateCalendarEvent;

$templateCalendarEvent = TemplateCalendarEvent::find($id);
$calendarEventUpdated  = $templateCalendarEvent->editCalendarEvent(Carbon::parse('2017-08-30'), [
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
Do NOT forget the cron job 
```bash
php artisan generate:calendar-event
```

## Credits
* [T1k3](https://github.com/t1k3hu)

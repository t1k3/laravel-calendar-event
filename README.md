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
Create CalendarEvent
```php
$calendarEvent = new CalendarEvent();
$calendarEvent->createCalendarEvent([
    'start_date'                    => '2017-08-25',
    'start_time'                    => 16,
    'end_time'                      => 17,
    'description'                   => 'Lorem ipsum',
    'is_recurring'                  => true,
    'frequence_number_of_recurring' => 1,
    'frequence_type_of_recurring'   => RecurringFrequenceType::WEEK,
    'is_public'                     => true,
    'end_of_recurring'              => '2017-09-08'
]);
```

Edit CalendarEvent
```php
$calendarEvent = CalendarEvent::find($id);
$calendarEvent->editCalendarEvent([
    'start_date'   => '2017-08-26',
    'is_recurring' => false,
]);
```

Generate next CalendarEvents from Console
```bash
php artisan generate:calendar-event
```

## Credits
* [T1k3](https://github.com/t1k3hu)

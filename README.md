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

## Credits
* [T1k3](https://github.com/t1k3hu)

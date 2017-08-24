<?php

namespace T1k3\LaravelCalendarEvent\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Class TestCase
 * @package T1k3\LaravelCalendarEvent\Tests
 */
abstract class TestCase extends BaseTestCase
{
//    use DatabaseTransactions;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setUpFactory();
        $this->setUpDatabase();
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Configure the factory
     */
    private function setUpFactory()
    {
        $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    /**
     * Configure the database
     * SQLite
     */
    private function setUpDatabase()
    {
//        $this->artisan('migrate', [
//            '--database' => 'testing',
//            '--path'     => __DIR__ . '/../src/database/migrations',
//        ]);

        (new \CreateTemplateCalendarEventsTable)->up();
        (new \CreateCalendarEventsTable)->up();
    }
}

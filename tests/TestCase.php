<?php

namespace T1k3\LaravelCalendarEvent\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use T1k3\LaravelCalendarEvent\ServiceProvider as LaravelCalendarEventServiceProvider;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class TestCase
 * @package T1k3\LaravelCalendarEvent\Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Console output
     * @var string
     */
    protected $consoleOutput;

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
     * Teardown
     */
    public function tearDown()
    {
        $this->consoleOutput = '';

        (new \CreateTemplateCalendarEventsTable)->down();
        (new \CreateCalendarEventsTable)->down();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaravelCalendarEventServiceProvider::class];
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(Kernel::class, \Orchestra\Testbench\Console\Kernel::class);
    }

    /**
     * @return mixed
     */
    public function getConsoleOutput()
    {
        return $this->consoleOutput ?: $this->consoleOutput = $this->app[Kernel::class]->output();
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

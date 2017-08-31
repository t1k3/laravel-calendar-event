<?php

namespace T1k3\LaravelCalendarEvent\Tests\Unit\config;


use T1k3\LaravelCalendarEvent\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function getConfigFromFile()
    {
        $config = [
            "user"  => [
                "model" => null
            ],
            "place" => [
                "model" => null
            ]
        ];
        $path   = __DIR__ . '/../../../src/config/calendar-event.php';

        $this->assertFileExists($path);
        $this->assertEquals(include $path, $config);
    }
}
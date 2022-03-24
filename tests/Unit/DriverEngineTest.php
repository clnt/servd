<?php

namespace Tests\Unit;

use App\DriverEngine;
use App\Drivers\Exceptions\DriverNotFound;
use App\Drivers\Laravel;
use Tests\Support\TestDriver;
use Tests\TestCase;

class DriverEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('drivers', [
            Laravel::class,
            TestDriver::class,
        ]);

        app()->instance(DriverEngine::class, null);
    }

    /** @test */
    public function it_can_instantiate_the_driver_engine_with_static_make_method(): void
    {
        $this->assertInstanceOf(DriverEngine::class, DriverEngine::make([]));
    }

    /** @test */
    public function it_can_resolve_the_driver_engine_from_the_ioc(): void
    {
        $result = app(DriverEngine::class);

        $this->assertInstanceOf(DriverEngine::class, $result);
    }

    /** @test */
    public function it_can_get_the_expected_available_drivers(): void
    {
        $drivers = app(DriverEngine::class)->getAvailableDrivers();

        $this->assertContains(Laravel::class, $drivers);
    }

    /** @test */
    public function it_can_detect_the_driver_at_the_given_path(): void
    {
        $path = base_path('tests/Support/Projects/Laravel');

        $this->assertEquals('laravel', app(DriverEngine::class)->detect($path));
    }

    /** @test */
    public function it_keys_drivers_by_identifier(): void
    {
        config()->set('drivers', [
            Laravel::class,
            TestDriver::class,
        ]);

        $engine = app(DriverEngine::class);
        $drivers = $engine->getAvailableDrivers();

        $this->assertEquals([
            'laravel' => Laravel::class,
            'test-driver' => TestDriver::class,
        ], $drivers);
    }

    /** @test */
    public function it_can_get_a_driver_by_identifier(): void
    {
        $engine = app(DriverEngine::class);

        $result = $engine->getDriverByIdentifier('test-driver');

        $this->assertInstanceOf(TestDriver::class, $result);
    }

    /** @test */
    public function it_throws_driver_not_found_exception_if_given_identifier_invalid(): void
    {
        try {
            $engine = app(DriverEngine::class);
            $engine->getDriverByIdentifier('invalid-driver');
        } catch (DriverNotFound $exception) {
            $this->assertEquals('The specified driver could not be found', $exception->getMessage());
            return;
        }

        $this->fail('DriverNotFound exception not thrown');
    }
}

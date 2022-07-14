<?php

namespace Tests\Unit\Drivers;

use App\Drivers\Laravel;
use Tests\TestCase;

class LaravelTest extends TestCase
{
    /** @test */
    public function it_can_create_a_new_laravel_driver_instance_using_make(): void
    {
        $this->assertInstanceOf(Laravel::class, Laravel::make());
    }

    /** @test */
    public function it_can_get_the_expected_identifier(): void
    {
        $this->assertEquals('laravel', Laravel::make()->identifier());
    }

    /** @test */
    public function it_can_get_the_expected_directory_root(): void
    {
        $this->assertEquals('/public', Laravel::make()->directoryRoot());
    }

    /** @test */
    public function it_can_get_the_expected_scheduler(): void
    {
        $this->assertEquals('', Laravel::make()->scheduler());
    }

    /** @test */
    public function it_can_detect_a_laravel_instance(): void
    {
        $testDirectoryPath = base_path('tests/Support/Projects/Laravel');

        $this->assertTrue(Laravel::make()->detect($testDirectoryPath));
    }
}

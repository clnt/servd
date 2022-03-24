<?php

namespace Unit\Drivers;

use App\Drivers\Unknown;
use Tests\TestCase;

class UnknownTest extends TestCase
{
    /** @test */
    public function it_can_create_a_new_unknown_driver_instance_using_make(): void
    {
        $this->assertInstanceOf(Unknown::class, Unknown::make());
    }

    /** @test */
    public function it_can_get_the_expected_identifier(): void
    {
        $this->assertEquals('unknown', Unknown::make()->identifier());
    }

    /** @test */
    public function it_can_get_the_expected_directory_root(): void
    {
        $this->assertEquals('', Unknown::make()->directoryRoot());
    }

    /** @test */
    public function it_can_get_the_expected_scheduler(): void
    {
        $this->assertEquals('', Unknown::make()->scheduler());
    }

    /** @test */
    public function it_can_detect_an_unknown_instance(): void
    {
        $testDirectoryPath = base_path('tests/Support/Projects/Unknown');

        $this->assertTrue(Unknown::make()->detect($testDirectoryPath));
    }
}

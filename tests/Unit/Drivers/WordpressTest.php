<?php

namespace Tests\Unit\Drivers;

use App\Drivers\Wordpress;
use Tests\TestCase;

class WordpressTest extends TestCase
{
    /** @test */
    public function it_can_create_a_new_wordpress_driver_instance_using_make(): void
    {
        $this->assertInstanceOf(Wordpress::class, Wordpress::make());
    }

    /** @test */
    public function it_can_get_the_expected_identifier(): void
    {
        $this->assertEquals('wordpress', Wordpress::make()->identifier());
    }

    /** @test */
    public function it_can_get_the_expected_directory_root(): void
    {
        $this->assertEquals('/', Wordpress::make()->directoryRoot());
    }

    /** @test */
    public function it_can_get_the_expected_scheduler(): void
    {
        $this->assertEquals('', Wordpress::make()->scheduler());
    }

    /** @test */
    public function it_can_detect_a_wordpress_instance(): void
    {
        $testDirectoryPath = base_path('tests/Support/Projects/Wordpress');

        $this->assertTrue(Wordpress::make()->detect($testDirectoryPath));
    }
}

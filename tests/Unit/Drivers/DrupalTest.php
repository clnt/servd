<?php

namespace Unit\Drivers;

use App\Drivers\Drupal;
use Tests\TestCase;

class DrupalTest extends TestCase
{
    /** @test */
    public function it_can_create_a_new_drupal_driver_instance_using_make(): void
    {
        $this->assertInstanceOf(Drupal::class, Drupal::make());
    }

    /** @test */
    public function it_can_get_the_expected_identifier(): void
    {
        $this->assertEquals('drupal', Drupal::make()->identifier());
    }

    /** @test */
    public function it_can_get_the_expected_default_directory_root(): void
    {
        $this->assertEquals('/', Drupal::make()->directoryRoot());
    }

    /** @test */
    public function it_checks_for_a_web_directory_if_given_project_path(): void
    {
        $this->assertEquals(
            '/web',
            Drupal::make()->directoryRoot(base_path('tests/Support/Projects/Drupal'))
        );
    }

    /** @test */
    public function it_returns_the_default_if_web_directory_not_found_and_given_project_path(): void
    {
        $this->assertEquals(
            '/',
            Drupal::make()->directoryRoot(base_path('tests/Support/Projects/Drupal/core'))
        );
    }

    /** @test */
    public function it_returns_the_default_if_web_directory_not_found_and_given_project_path_invalid(): void
    {
        $this->assertEquals(
            '/',
            Drupal::make()->directoryRoot(base_path('tests/Support/Projects/Drupal/invalid'))
        );
    }

    /** @test */
    public function it_can_get_the_expected_scheduler(): void
    {
        $this->assertEquals('', Drupal::make()->scheduler());
    }

    /** @test */
    public function it_can_detect_a_drupal_instance(): void
    {
        $testDirectoryPath = base_path('tests/Support/Projects/Drupal');

        $this->assertTrue(Drupal::make()->detect($testDirectoryPath));
    }
}

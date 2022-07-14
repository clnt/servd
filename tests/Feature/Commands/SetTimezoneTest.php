<?php

namespace Tests\Feature\Commands;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SetTimezoneTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_run_the_set_timezone_command(): void
    {
        $this->setupDefaultSettingsAndServices();

        $this->assertEquals('UTC', Setting::get(Setting::KEY_TIMEZONE));

        $this->artisan('set:timezone')
            ->expectsQuestion(
                'What is your timezone? This must be a valid IANA timezone i.e. Europe/London',
                'Europe/London'
            )
            ->expectsOutput('Updated timezone setting ✔')
            ->expectsOutput('Updated docker files ✔')
            ->expectsOutput('Run the rebuild command for changes to take effect')
            ->assertSuccessful();

        $this->assertEquals('Europe/London', Setting::get(Setting::KEY_TIMEZONE));
    }

    /** @test */
    public function it_returns_an_error_message_if_an_exception_is_thrown_during_configuration(): void
    {
        $this->setupDefaultSettingsAndServices();

        $this->assertEquals('UTC', Setting::get(Setting::KEY_TIMEZONE));
        $this->assertGreaterThan(1, Service::count());

        Service::truncate();

        $this->artisan('set:timezone')
            ->expectsQuestion(
                'What is your timezone? This must be a valid IANA timezone i.e. Europe/London',
                'Europe/London'
            )
            ->expectsOutput('Updated timezone setting ✔')
            ->expectsOutput('An error occurred updating the docker files ✖')
            ->assertSuccessful();

        $this->assertEquals('Europe/London', Setting::get(Setting::KEY_TIMEZONE));
    }
}

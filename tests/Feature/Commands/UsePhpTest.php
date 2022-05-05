<?php

namespace Feature\Commands;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Tests\TestCase;

class UsePhpTest extends TestCase
{
    use DatabaseMigrations;

    protected MockInterface $cli;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cli = $this->mockCli();
        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_run_the_use_command(): void
    {
        $this->setupDefaultSettingsAndServices();

        $this->cli->shouldReceive('execRealTime')->times(2);

        $this->assertEquals('8.1', Setting::get(Setting::KEY_PHP_VERSION));

        $this->artisan('use', ['version' => '8.0'])
            ->expectsOutput('Updating PHP version setting: ✔')
            ->expectsOutput('Scanning working directory for projects: ✔')
            ->expectsOutput('Rebuilding Docker files: ✔')
            ->expectsOutput('Rebuilding project configurations: ✔')
            ->expectsOutput('Rebuilding service: ✔')
            ->expectsConfirmation('Service rebuilt, would you like to restart containers now?', 'yes')
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();

        $this->assertEquals('8.0', Setting::get(Setting::KEY_PHP_VERSION));
        $this->assertEquals('8.0', Service::where('service_name', 'servd')->first()->version);
    }

    /** @test */
    public function it_can_check_to_see_if_the_php_version_specified_is_supported(): void
    {
        $this->artisan('use', ['version' => '7.0'])
            ->expectsOutput('The given PHP version 7.0 is not supported ✖');
    }
}

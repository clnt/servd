<?php

namespace Feature\Commands;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DrushInstallTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_drush_install_command(): void
    {
        $this->artisan('drush:install')
            ->expectsChoice('Which version of Drush would you like to use?', '11', ['11', '10'])
            ->expectsOutput('Drush version 11 selected successfully ✔')
            ->assertExitCode(0);

        $this->assertEquals('11', Setting::where('key', Setting::KEY_DRUSH_VERSION)->firstOrFail()->value);
    }
}

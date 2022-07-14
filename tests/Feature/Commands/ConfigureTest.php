<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConfigureTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_configure_command(): void
    {
        $this->setupDefaultSettingsAndServices();

        $this->artisan('configure')
            ->expectsOutput('Scanning working directory for projects: ✔')
            ->expectsOutput('Rebuilding Docker files: ✔')
            ->expectsOutput('Rebuilding project configurations: ✔')
            ->assertSuccessful();
    }
}

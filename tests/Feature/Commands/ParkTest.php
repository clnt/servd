<?php

namespace Tests\Feature\Commands;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ParkTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_servd_park_command(): void
    {
        $this->artisan('park')
            ->expectsOutput('Setting the servd working directory to the current directory: ' . getcwd())
            ->expectsOutput('Working directory successfully updated')
            ->assertExitCode(0);

        $this->assertEquals(getcwd(), Setting::where('key', Setting::KEY_WORKING_DIRECTORY)->firstOrFail()->value);
    }
}

<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ScanTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_servd_scan_command(): void
    {
        $this->setupDefaultSettingsAndServices();

        $this->mockCli()->shouldReceive('execRealTime')->times(2);

        $this->artisan('scan')->assertSuccessful();
    }
}

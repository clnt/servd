<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SecureTrustTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_asks_for_confirmation_and_runs_command_if_unix_detected(): void
    {
        $this->markTestSkipped('Not ready');
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('CA certificate found ✔')
            ->expectsConfirmation(
                'This command requires elevated privileges, do you wish to continue?',
                'yes'
            );
    }

    public function it_returns_an_informational_message_if_windows_is_detected(): void
    {
        // Do method
    }
}

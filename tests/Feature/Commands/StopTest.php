<?php

namespace Feature\Commands;

use Tests\TestCase;

class StopTest extends TestCase
{
    /** @test */
    public function it_can_run_the_stop_command(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->fakeDataDirectoryPath()
            . 'docker-compose.yml -p servd down'
        )->once();

        $this->artisan('stop')
            ->expectsOutput('Stopping the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_stop_command_for_a_given_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->fakeDataDirectoryPath()
            . 'docker-compose.yml -p servd stop redis'
        )->once();

        $this->artisan('stop', ['service' => 'redis'])
            ->expectsOutput('Stopping the configured services: ✔')
            ->assertSuccessful();
    }
}

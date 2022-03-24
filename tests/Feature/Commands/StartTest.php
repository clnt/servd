<?php

namespace Feature\Commands;

use Tests\TestCase;

class StartTest extends TestCase
{
    /** @test */
    public function it_can_run_the_start_command(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->fakeDataDirectoryPath()
            . 'docker-compose.yml -p servd up -d --remove-orphans'
        )->once();

        $this->artisan('start')
            ->expectsOutput('Starting the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_start_command_for_a_given_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->fakeDataDirectoryPath()
            . 'docker-compose.yml -p servd start redis'
        )->once();

        $this->artisan('start', ['service' => 'redis'])
            ->expectsOutput('Starting the configured services: ✔')
            ->assertSuccessful();
    }
}

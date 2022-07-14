<?php

namespace Tests\Feature\Commands;

use Mockery\MockInterface;
use Tests\TestCase;

class RestartTest extends TestCase
{
    protected MockInterface $cli;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cli = $this->mockCli();
        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_run_the_restart_command(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd restart'
        )->once();

        $this->artisan('restart')
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_restart_command_for_a_given_service(): void
    {
        $this->cli->shouldReceive('execRealTime')->with('docker restart redis')->once();

        $this->artisan('restart', ['service' => 'redis'])
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_restart_command_with_optional_rebuild(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build'
        )->once();

        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd restart'
        )->once();

        $this->artisan('restart', ['--rebuild' => true])
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();
    }
}

<?php

namespace Feature\Commands;

use Mockery\MockInterface;
use Tests\TestCase;

class RebuildTest extends TestCase
{
    protected MockInterface $cli;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cli = $this->mockCli();
        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_run_the_rebuild_command(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build'
        )->once();

        $this->artisan('rebuild')
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_rebuild_command_and_restart_services(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build'
        )->once();

        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd restart'
        )->once();

        $this->artisan('rebuild')
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?', 'yes')
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_rebuild_command_for_a_given_service_and_restart_service(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build redis'
        )->once();

        $this->cli->shouldReceive('execRealTime')->with('docker restart redis')->once();

        $this->artisan('rebuild', ['service' => 'redis'])
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?', 'yes')
            ->expectsOutput('Restarting the configured services: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_rebuild_command_for_a_given_service(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build redis'
        )->once();

        $this->artisan('rebuild', ['service' => 'redis'])
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_rebuild_command_with_optional_update(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd pull'
        )->once();

        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build'
        )->once();

        $this->artisan('rebuild', ['--update' => true])
            ->expectsOutput('Updating the configured services: ✔')
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_rebuild_command_for_a_given_service_with_optional_update(): void
    {
        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd pull redis'
        )->once();

        $this->cli->shouldReceive('execRealTime')->with(
            'docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd build redis'
        )->once();

        $this->artisan('rebuild', ['service' => 'redis', '--update' => true])
            ->expectsOutput('Updating the configured services: ✔')
            ->expectsOutput('Rebuilding the configured services: ✔')
            ->expectsConfirmation('Services rebuilt, would you like to restart containers now?')
            ->assertSuccessful();
    }
}

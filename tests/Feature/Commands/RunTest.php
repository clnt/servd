<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Tests\TestCase;

class RunTest extends TestCase
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
    public function it_can_run_the_run_command_to_execute_a_command_within_the_core_container(): void
    {
        $this->cli->shouldReceive('execRealTime')
            ->with('docker exec -w /var/www/' . basename(getcwd()) . ' servd_core php artisan cache:clear')
            ->once();

        $this->artisan('run', ['argument' => 'php artisan cache:clear'])
            ->expectsOutput('Running command in servd_core container: ✔')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_run_command_to_execute_a_command_within_the_given_container(): void
    {
        $this->cli->shouldReceive('execRealTime')
            ->with('docker exec -w /var/www/' . basename(getcwd()) . ' servd_mariadb mysql -V')
            ->once();

        $this->artisan('run', ['argument' => 'mysql -V', 'container' => 'servd_mariadb'])
            ->expectsOutput('Running command in servd_mariadb container: ✔')
            ->assertSuccessful();
    }
}

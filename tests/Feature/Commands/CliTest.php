<?php

namespace Feature\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Tests\TestCase;

class CliTest extends TestCase
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
    public function it_can_run_the_cli_command_to_open_interactive_shell_for_the_core_container(): void
    {
        $this->cli->shouldReceive('doNotTimeout')->once();
        $this->cli->shouldReceive('passthrough')
            ->with('docker exec -itw /var/www/' . basename(getcwd()) . ' servd_core /bin/sh')
            ->once();

        $this->artisan('cli')
            ->expectsOutput('Opening interactive shell into the servd_core container')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_run_the_cli_command_to_open_interactive_shell_for_a_given_container(): void
    {
        $this->cli->shouldReceive('doNotTimeout')->once();
        $this->cli->shouldReceive('passthrough')
            ->with('docker exec -itw /var/www/' . basename(getcwd()) . ' servd_mariadb /bin/sh')
            ->once();

        $this->artisan('cli', ['container' => 'servd_mariadb'])
            ->expectsOutput('Opening interactive shell into the servd_mariadb container')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_set_and_get_the_timeout(): void
    {
        $this->cli->shouldReceive('setTimeout')
            ->with(3600)
            ->andReturn($this->cli)
            ->once();

        $this->cli->shouldReceive('getTimeout')
            ->andReturn(3600)
            ->once();

        $this->cli->setTimeout(3600);

        $this->assertEquals(3600, $this->cli->getTimeout());
    }

    /** @test */
    public function it_handles_a_null_timeout_value(): void
    {
        $this->cli->shouldReceive('setTimeout')
            ->with(null)
            ->andReturn($this->cli)
            ->once();

        $this->cli->setTimeout(null);
    }

    /** @test */
    public function it_can_set_do_not_timeout(): void
    {
        $this->cli->shouldReceive('getTimeout')
            ->with()
            ->andReturn(config('servd.process_timeout'))
            ->once();

        $this->assertNotNull($this->cli->getTimeout());

        $this->cli->shouldReceive('doNotTimeout')
            ->with()
            ->andReturn($this->cli)
            ->once();

        $this->cli->doNotTimeout();
    }
}

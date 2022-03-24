<?php

namespace Unit\Console;

use App\Console\DockerComposeCommand;
use Tests\TestCase;

class DockerComposeCommandTest extends TestCase
{
    protected string $dataDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_create_a_new_docker_cli_command_via_static_make_method(): void
    {
        $command = DockerComposeCommand::make('up');

        $this->assertEquals('up', $command->getCommand());
    }

    /** @test */
    public function it_can_append_a_given_string_to_the_command(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->append('--test');

        $this->assertEquals('up --test', $command->getCommand());
    }

    /** @test */
    public function it_can_append_a_bash_command_with_call(): void
    {
        $command = DockerComposeCommand::make();
        $command->bash('-t ' . $this->dataDirectory);

        $this->assertEquals('bash -c "-t ' . $this->dataDirectory . '"', $command->getCommand());
    }

    /** @test */
    public function it_can_prepare_the_default_docker_compose_up_command(): void
    {
        $command = DockerComposeCommand::make('up -d --remove-orphans ');

        $this->assertEquals(
            'docker-compose -f ' . $this->dataDirectory . 'docker-compose.yml -p servd up -d --remove-orphans',
            $command->prepare()
        );
    }

    /** @test */
    public function it_can_set_the_command_as_interactive(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->interactive();

        $this->assertTrue($command->isInteractive());
    }

    /** @test */
    public function it_can_check_if_the_command_is_not_interactive(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->notInteractive();

        $this->assertFalse($command->isInteractive());
    }

    /** @test */
    public function it_can_set_the_command_as_real_time(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->realTime();

        $this->assertTrue($command->isRealTime());
    }

    /** @test */
    public function it_can_check_if_the_command_is_not_real_time(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->notRealTime();

        $this->assertFalse($command->isRealTime());
    }

    /** @test */
    public function it_can_set_the_timeout_for_the_cli(): void
    {
        $this->mockCli()->shouldReceive('setTimeout')->with(1000)->once();
        $command = DockerComposeCommand::make('up');
        $command->setTimeout(1000);
    }

    /** @test */
    public function it_can_get_the_cli_timeout(): void
    {
        $command = DockerComposeCommand::make('up');
        $command->setTimeout(1000);

        $this->assertEquals(1000, $command->getCli()->getTimeout());
    }

    /** @test */
    public function it_can_set_do_not_timeout_for_the_cli(): void
    {
        $this->mockCli()->shouldReceive('doNotTimeout')->once();
        $command = DockerComposeCommand::make('up');
        $command->doNotTimeout();
    }

    /** @test */
    public function it_can_perform_the_command(): void
    {
        $this->mockCli()->shouldReceive('exec')
            ->with('docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd up -d --remove-orphans')
            ->once();
        $command = DockerComposeCommand::make('up -d --remove-orphans ');
        $command->perform();
    }

    /** @test */
    public function it_can_perform_the_interactive_command(): void
    {
        $this->mockCli()->shouldReceive('passthrough')
            ->with('docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd up -d --remove-orphans')
            ->once();
        $command = DockerComposeCommand::make('up -d --remove-orphans ');
        $command->interactive()->perform();
    }

    /** @test */
    public function it_can_perform_the_realtime_command(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')
            ->with('docker-compose -f ' . $this->dataDirectory
            . 'docker-compose.yml -p servd up -d --remove-orphans')
            ->once();
        $command = DockerComposeCommand::make('up -d --remove-orphans ');
        $command->realTime()->perform();
    }
}

<?php

namespace Tests\Unit\Console;

use App\Console\DockerCommand;
use Tests\TestCase;

class DockerCommandTest extends TestCase
{
    protected string $dataDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_can_create_a_new_docker_cli_command_with_argument_via_static_make_method(): void
    {
        $command = DockerCommand::make('exec');

        $this->assertEquals('exec', $command->getCommand());
    }

    /** @test */
    public function it_can_get_the_given_argument(): void
    {
        $command = DockerCommand::make('exec', 'php -v');

        $this->assertEquals('php -v', $command->getArgument());
    }

    /** @test */
    public function it_can_prepare_the_docker_exec_command(): void
    {
        $command = DockerCommand::make('exec', 'php artisan cache:clear', 'servd_core');

        $this->assertEquals(
            'docker exec servd_core php artisan cache:clear',
            $command->prepare()
        );
    }
}

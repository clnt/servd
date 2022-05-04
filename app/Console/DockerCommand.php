<?php

namespace App\Console;

class DockerCommand extends DockerComposeCommand
{
    protected string $argument;

    protected ?string $container;

    public string $command;

    public function __construct(Cli $cli, string $command = '', string $argument = '', ?string $container = null)
    {
        $this->argument = $argument;
        $this->container = $container;

        parent::__construct($cli, $command);
    }

    public static function make(string $command = '', string $argument = '', ?string $container = null): self
    {
        return app(self::class, [
            'cli' => app(Cli::class),
            'command' => $command,
            'argument' => $argument,
            'container' => $container,
        ]);
    }

    public function prepare(): string
    {
        return trim("docker {$this->command} {$this->container} {$this->argument}");
    }

    public function getArgument(): string
    {
        return $this->argument;
    }
}

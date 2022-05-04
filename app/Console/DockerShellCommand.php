<?php

namespace App\Console;

class DockerShellCommand extends CliCommand
{
    protected ?string $container;

    protected string $command;

    protected Cli $cli;

    public function __construct(Cli $cli, string $command = '', ?string $container = null)
    {
        $this->cli = $cli;
        $this->command = $command;
        $this->container = $container;

        parent::__construct($cli, $command);
    }

    public static function make(string $command = '', ?string $container = null): self
    {
        return app(self::class, [
            'cli' => app(Cli::class),
            'command' => $command,
            'container' => $container,
        ]);
    }

    public function prepare(): string
    {
        return trim("docker exec {$this->container} /bin/sh -c \"{$this->command}\"");
    }
}

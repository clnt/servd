<?php

namespace App\Console;

use App\ServDocker;

class DockerComposeCommand extends CliCommand
{
    public static function make(string $command = ''): self
    {
        return app(self::class, ['cli' => app(Cli::class), 'command' => $command]);
    }

    /**
     * Prepare the full command string.
     */
    public function prepare(): string
    {
        return trim(
            'docker-compose -f '
            . app(ServDocker::class)->getDataDirectory() . 'docker-compose.yml'
            . ' -p servd '
            . $this->command
        );
    }
}

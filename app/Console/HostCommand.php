<?php

namespace App\Console;

class HostCommand extends CliCommand
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
        return $this->command;
    }
}

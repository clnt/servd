<?php

namespace App\Concerns;

trait ConsoleHelpers
{
    public function successMessage(string $message): void
    {
        $this->getOutput()->writeLn("<info>${message} ✔</info>");
    }

    public function errorMessage(string $message): void
    {
        $this->getOutput()->writeLn("<error>${message} ✖</error>");
    }

    public function getArgument(string $name = 'service'): ?string
    {
        return $this->hasArgument($name) ? $this->argument($name) : null;
    }
}

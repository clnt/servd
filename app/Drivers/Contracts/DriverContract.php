<?php

namespace App\Drivers\Contracts;

interface DriverContract
{
    public function identifier(): string;

    public function directoryRoot(): string;

    public function scheduler(): ?string;

    public function detect(string $path): bool;
}

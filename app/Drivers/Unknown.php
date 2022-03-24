<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class Unknown implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'unknown';
    }

    public function directoryRoot(): string
    {
        return '';
    }

    public function scheduler(): ?string
    {
        return null;
    }

    public function detect(string $path): bool
    {
        return true;
    }
}

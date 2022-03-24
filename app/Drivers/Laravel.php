<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class Laravel implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'laravel';
    }

    public function directoryRoot(): string
    {
        return '/public';
    }

    public function scheduler(): ?string
    {
        return '';
    }

    public function detect(string $path): bool
    {
        $path .= $this->directoryRoot() . '/index.php';

        return file_exists($path) && str_contains(file_get_contents($path), 'Laravel');
    }
}

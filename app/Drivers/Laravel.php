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
        return file_exists(
            $path . $this->directoryRoot() . '/index.php'
        ) && file_exists(
            $path . '/artisan'
        );
    }
}

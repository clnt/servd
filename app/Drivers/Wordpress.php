<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class Wordpress implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'wordpress';
    }

    public function directoryRoot(): string
    {
        return '/';
    }

    public function scheduler(): ?string
    {
        return '';
    }

    public function detect(string $path): bool
    {
        return file_exists(
            $path . $this->directoryRoot() . '/wp-config.php'
        ) || file_exists($path . $this->directoryRoot() . '/wp-config-sample.php');
    }
}

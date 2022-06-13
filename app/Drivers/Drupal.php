<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class Drupal implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'drupal';
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
            $path . $this->directoryRoot() . '/misc/drupal.js'
        ) || file_exists($path . $this->directoryRoot() . '/core/lib/Drupal.php') || str_contains(
            file_get_contents($path . $this->directoryRoot() . '/index.php'),
            'Drupal'
        );
    }
}

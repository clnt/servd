<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class GenericPhp implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'generic_php';
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
        $path .= $this->directoryRoot();

        $files = [
            'index.php',
            'Index.php',
        ];

        return file_exists($path) && $this->getFilenamesFromPath($path)
            ->contains(function (string $item) use ($files): bool {
                return in_array($item, $files, true);
            });
    }
}

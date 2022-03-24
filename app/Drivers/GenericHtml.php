<?php

namespace App\Drivers;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class GenericHtml implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'generic_html';
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
            'index.html',
            'index.htm',
            'default.html',
            'default.htm',
            'Index.htm',
            'Index.html',
            'Default.html',
            'Default.htm',
        ];

        return file_exists($path) && $this->getFilenamesFromPath($path)
            ->contains(function (string $item) use ($files): bool {
                return in_array($item, $files, true);
            });
    }
}

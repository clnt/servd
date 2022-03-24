<?php

namespace Tests\Support;

use App\Drivers\Concerns\Driver;
use App\Drivers\Contracts\DriverContract;

class TestDriver implements DriverContract
{
    use Driver;

    public function identifier(): string
    {
        return 'test-driver';
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
        $path .= $this->directoryRoot() . '/index.html';

        return file_exists($path) && str_contains(file_get_contents($path), 'Test Project');
    }
}

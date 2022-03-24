<?php

namespace App;

use App\Drivers\Exceptions\DriverNotFound;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DriverEngine
{
    public const DRIVER_UNKNOWN = 'unknown';

    protected array $drivers;

    public function __construct(?array $drivers)
    {
        $this->drivers = $this->keyDriversByIdentifier($drivers) ?? [];
    }

    public static function make(?array $drivers): self
    {
        return new self($drivers);
    }

    public function getAvailableDrivers(): array
    {
        return $this->drivers;
    }

    public function detect(string $path): ?string
    {
        $driver = collect($this->drivers)->filter(function (string $driver) use ($path): bool {
            return $driver::make()->detect($path);
        });

        if ($driver->count() === 0) {
            return null;
        }

        if ($driver->count() > 1) { // phpcs:ignore
            // We have more than one driver detected, is it possible to get the user to pick?
        }

        return $driver->first()::make()->identifier();
    }

    public function getDriverByIdentifier(string $identifier): object
    {
        throw_if(Arr::exists($this->drivers, Str::snake($identifier)) === false, new DriverNotFound);

        return Arr::get($this->drivers, $identifier)::make();
    }

    private function keyDriversByIdentifier(array $drivers): array
    {
        return collect($drivers)->mapWithKeys(function (string $driver): array {
            return [$driver::make()->identifier() => $driver];
        })->toArray();
    }
}

<?php

namespace App\Drivers\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use SplFileInfo;

trait Driver
{
    public static function make(): self
    {
        return new self();
    }

    public function nginxConfiguration(string $dataDirectory, string $globalConfigurationDirectory): string
    {
        $stubDirectory = base_path('stubs/configs/nginx/drivers/' . $this->identifier());
        $driverConfigurationDirectory = $dataDirectory . 'services/servd/config/nginx/drivers/' . $this->identifier();

        $configuration = $this->reduceMultipleFilesToString(File::files($stubDirectory));

        if (File::exists($globalConfigurationDirectory)) {
            $configuration .= $this->reduceMultipleFilesToString(File::files($globalConfigurationDirectory));
        }

        if (File::exists($driverConfigurationDirectory)) {
            $configuration .= $this->reduceMultipleFilesToString(File::files($driverConfigurationDirectory));
        }

        return $configuration;
    }

    protected function reduceMultipleFilesToString(array $files): string
    {
        if (count($files) === 0) {
            return '';
        }

        return collect($files)->reduce(static function (?string $existing, string $fileName): string {
            return $existing . file_get_contents($fileName);
        });
    }

    protected function getFilenamesFromPath(string $path): Collection
    {
        return collect(File::files($path))->map(static function (SplFileInfo $file): string {
            return $file->getFilename();
        });
    }
}

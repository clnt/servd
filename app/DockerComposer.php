<?php

namespace App;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class DockerComposer
{
    protected ?Collection $services;

    protected ?string $dataDirectory;

    protected string $stubsPath;

    protected ?string $stubs;

    protected ?string $dependsOn;

    protected ?string $volumes;

    protected ?string $dockerCompose;

    public function __construct()
    {
        $this->stubsPath = base_path('stubs/docker');
    }

    public static function make(): self
    {
        return new self();
    }

    public function buildDockerCompose(): self
    {
        $this->dataDirectory = Setting::get(Setting::KEY_DATA_DIRECTORY);

        // Concatenate all enabled service docker compose stubs.
        $this->concatStubs();

        // Build Volumes for enabled services.
        $this->createVolumeDefinitions();

        // Build depends_on for enabled services.
        $this->createDependsOnDefinitions();

        // Generate docker env file in data directory.
        $this->createDockerEnvironmentFile();

        // Compile single docker compose file and copy to data directory.
        $this->createDockerComposeFile();

        // Copy over service build directories containing Dockerfiles and configs.
        $this->createServiceBuildDirectories();

        return $this;
    }

    protected function createServiceBuildDirectories(): void
    {
        $this->services->filter(static function (Service $service): bool {
            return (bool) $service->should_build;
        })->each(function (Service $service): void {
            File::copyDirectory(
                "{$this->stubsPath}/{$service->service_name}/{$service->version}/build",
                "{$this->dataDirectory}services/{$service->service_name}/build"
            );
        });
    }

    protected function createDependsOnDefinitions(): void
    {
        $this->dependsOn = $this->services
            ->filter(function (Service $service) {
                return in_array(
                    $service->type,
                    [Service::TYPE_DATABASE, Service::TYPE_MEMORY_STORE],
                    true
                );
            })->map(function (Service $service) {
                return "      - {$service->service_name}";
            })->whenNotEmpty(function ($collection) {
                return $collection->prepend('depends_on:');
            })->implode("\n");
    }

    protected function createDockerComposeFile(): void
    {
        $this->dockerCompose = file_get_contents($this->stubsPath . '/docker-compose.stub');

        $this->dockerCompose = str_replace(
            ['{{$dependsOn}}','{{$services}}','{{$volumes}}'],
            [
                filled($this->dependsOn) === false ? '' : '    ' . $this->dependsOn,
                $this->stubs,
                $this->volumes,
            ],
            $this->dockerCompose
        );

        $this->removeEmptyLines();

        File::put($this->dataDirectory . 'docker-compose.yml', $this->dockerCompose);
    }

    protected function concatStubs(): void
    {
        $this->stubs = rtrim(
            $this->services->map(function (Service $service) {
                if ($service->single_stub) {
                    return str_replace('{{$version}}', $service->version, file_get_contents(
                        "{$this->stubsPath}/{$service->service_name}/{$service->service_name}.stub"
                    ));
                }

                return file_get_contents(
                    "{$this->stubsPath}/{$service->service_name}/{$service->version}/{$service->service_name}.stub"
                );
            })->implode('')
        );
    }

    protected function createVolumeDefinitions(): void
    {
        $this->volumes = $this->services
            ->filter(function (Service $service) {
                return (bool) $service->has_volume;
            })->map(function (Service $service) {
                return "    {$service->service_name}_data:\n        driver: local";
            })->whenNotEmpty(function ($collection) {
                return $collection->prepend('volumes:');
            })->implode("\n");
    }

    protected function createDockerEnvironmentFile(): void
    {
        $env = str_replace(
            [
                '{{$timezone}}',
                '{{$workingDirectory}}',
                '{{$nodeVersion}}',
                '{{$phpVersion}}',
                '{{$composerVersion}}',
                '{{$dataDirectory}}',
                '{{$drushVersion}}',
                '{{$installDrush}}',
            ],
            [
                Setting::get(Setting::KEY_TIMEZONE, 'UTC'),
                Setting::get(Setting::KEY_WORKING_DIRECTORY),
                Setting::get(Setting::KEY_NODE_VERSION),
                Setting::get(Setting::KEY_PHP_VERSION),
                Setting::get(Setting::KEY_DATA_DIRECTORY),
                Setting::get(Setting::KEY_DRUSH_VERSION),
                Setting::get(Setting::KEY_DRUSH_VERSION) !== null ? 'true' : 'false',
            ],
            file_get_contents($this->stubsPath . '/.env.stub')
        );

        File::put($this->dataDirectory . '.env', $env);
    }

    protected function removeEmptyLines(): void
    {
        preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $this->dockerCompose);
    }

    public function setServices(Collection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getServices(): ?Collection
    {
        return $this->services;
    }
}

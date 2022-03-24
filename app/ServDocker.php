<?php

namespace App;

use App\Console\DockerCommand;
use App\Console\DockerComposeCommand;
use App\Drivers\Exceptions\NoServicesEnabled;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ServDocker
{
    private string $directoryName = '.servd';

    private DockerComposer $composer;

    protected bool $isWindows = false;

    protected bool $isUnix = false;

    public function __construct(DockerComposer $composer)
    {
        $this->composer = $composer;
    }

    public static function make(): self
    {
        return app(self::class);
    }

    public function start(?string $service = null, ?bool $recreate = null): DockerComposeCommand
    {
        if ($service !== null) {
            return $this->runDockerComposeCommand("start {$service}");
        }

        $recreateArg = $recreate ? '--force-recreate ' : '';

        return $this->runDockerComposeCommand("up -d {$recreateArg}--remove-orphans {$service}");
    }

    public function stop(?string $service = null): DockerComposeCommand
    {
        if ($service !== null) {
            return $this->runDockerComposeCommand("stop {$service}");
        }

        return $this->runDockerComposeCommand("down");
    }

    public function restart(?string $service = null): DockerComposeCommand
    {
        if ($service !== null) {
            return $this->runDockerCommand(
                "restart {$service}",
                null,
                '',
            );
        }

        return $this->runDockerComposeCommand('restart');
    }

    public function build(?string $service = null): DockerComposeCommand
    {
        return $this->runDockerComposeCommand("build {$service}");
    }

    public function update(?string $service = null): DockerComposeCommand
    {
        return $this->runDockerComposeCommand("pull {$service}");
    }

    public function configure(): void
    {
        $enabledServices = Service::enabled()->where('type', '!=', Service::TYPE_CORE)->get();

        throw_if($enabledServices->count() === 0, new NoServicesEnabled);

        $directory = $this->getDataDirectory();

        // Build directory structure.
        $this->buildDirectoryStructure($enabledServices, $directory);

        // Build docker compose yaml
        $this->composer->setServices($enabledServices);
        $this->composer->buildDockerCompose();

        $this->setupDockerServiceFiles($directory);
    }

    public function run(string $argument, ?string $container): DockerCommand
    {
        return $this->runDockerCommand(
            "exec -w /var/www/{$this->getCurrentDirectoryName()}",
            $container,
            $argument,
        );
    }

    public function cli(string $container = 'servd_core'): DockerCommand
    {
        return $this->runInteractiveDockerCommand(
            "exec -itw /var/www/{$this->getCurrentDirectoryName()}",
            $container,
            '/bin/sh'
        );
    }

    public function setupDockerServiceFiles(string $directory): void
    {
        File::copyDirectory(base_path('stubs/docker/servd'), $directory . 'services/servd');
    }

    private function buildDirectoryStructure(Collection $services, string $directory): void
    {
        if (File::exists($directory . 'services') === false) {
            File::makeDirectory($directory . 'services');
        }

        $services->each(static function (Service $service) use ($directory): void {
            if ($service->has_volume) {
                return;
            }

            if (File::exists($directory . 'services/' . $service->service_name) === false) {
                File::makeDirectory($directory . 'services/' . $service->service_name, 0755, true);
            }

            if ($service->service_folders === null) {
                return;
            }

            collect(explode(',', $service->service_folders))
                ->each(function (string $path) use ($directory, $service): void {
                    $path = $directory . 'services/' . $service->service_name . '/' . $path;

                    if (File::exists($path)) {
                        return;
                    }

                    File::makeDirectory($path, 0755, true);
                });
        });
    }

    public function getDataDirectory(): ?string
    {
        if (Cache::has(Setting::KEY_DATA_DIRECTORY)) {
            return Cache::get(Setting::KEY_DATA_DIRECTORY);
        }

        return tap(Setting::get(Setting::KEY_DATA_DIRECTORY), static function (?string $dataDirectory): void {
            if ($dataDirectory === null) {
                return;
            }

            Cache::put(Setting::KEY_DATA_DIRECTORY, $dataDirectory);
        });
    }

    public function updateDataDirectoryPath(): string
    {
        if (filled($_SERVER['HOME'])) {
            return $this->setUnixDataDirectory();
        }

        if (filled($_SERVER['HOMEDRIVE']) && filled($_SERVER['HOMEPATH'])) {
            return $this->setWindowsDataDirectory();
        }
    }

    public function persistDataDirectoryPath(): string
    {
        $path = $this->getDataDirectory();

        if ($path === null) {
            $path = $this->updateDataDirectoryPath();
        }

        return Setting::updateOrCreateValue(
            [
                'key' => Setting::KEY_DATA_DIRECTORY,
            ],
            [
                'value' => $path,
            ]
        );
    }

    public function isUnix(): bool
    {
        if ($this->isUnix && $this->isWindows === false) {
            return true;
        }

        if ($this->isWindows) {
            return false;
        }

        return filled($_SERVER['HOME']);
    }

    public function isWindows(): bool
    {
        if ($this->isWindows && $this->isUnix === false) {
            return true;
        }

        if ($this->isUnix) {
            return false;
        }

        return filled($_SERVER['HOMEDRIVE']);
    }

    public function resetPlatformDetection(): self
    {
        $this->isWindows = false;
        $this->isUnix = false;

        return $this;
    }

    private function setUnixDataDirectory(): string
    {
        Cache::put(
            Setting::KEY_DATA_DIRECTORY,
            $_SERVER['HOME'] . '/' . $this->directoryName . '/'
        );

        $this->isUnix = true;

        return $this->getDataDirectory();
    }

    private function setWindowsDataDirectory(): string
    {
        Cache::put(
            Setting::KEY_DATA_DIRECTORY,
            $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '\\' . $this->directoryName . '\\'
        );

        $this->isWindows = true;

        return $this->getDataDirectory();
    }

    private function getCurrentDirectoryName(): string
    {
        return basename(getcwd());
    }

    private function runDockerComposeCommand(string $command): DockerComposeCommand
    {
        return tap(
            DockerComposeCommand::make($command),
            static function (DockerComposeCommand $command): void {
                $command->realTime()->perform();
            }
        );
    }

    private function runDockerCommand(string $command, ?string $container, string $argument): DockerCommand
    {
        return tap(
            DockerCommand::make($command, $argument, $container),
            static function (DockerCommand $command): void {
                $command->realTime()->perform();
            }
        );
    }

    private function runInteractiveDockerCommand(string $command, string $container, string $argument): DockerCommand
    {
        return tap(
            DockerCommand::make($command, $argument, $container),
            static function (DockerCommand $command): void {
                $command->interactive()->perform();
            }
        );
    }
}

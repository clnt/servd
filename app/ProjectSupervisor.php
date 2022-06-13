<?php

namespace App;

use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProjectSupervisor
{
    protected ServDocker $servd;

    protected DriverEngine $driverEngine;

    public function __construct(ServDocker $servd, DriverEngine $driverEngine)
    {
        $this->servd = $servd;
        $this->driverEngine = $driverEngine;
    }

    public static function make(): self
    {
        return app(__CLASS__);
    }

    public function setWorkingDirectory(string $path): Setting
    {
        return Setting::updateOrCreate(['key' => Setting::KEY_WORKING_DIRECTORY], ['value' => $path]);
    }

    public function getWorkingDirectory(): ?string
    {
        return Setting::get(Setting::KEY_WORKING_DIRECTORY);
    }

    public function scan(): int
    {
        $directories = File::directories($this->getWorkingDirectory());

        collect($directories)
            ->each(function (string $directory): void {
                $driver = $this->driverEngine->detect($directory);
                [$fullPath, $root] = $this->getDirectoryRootPath($driver, $directory);

                if (is_dir($fullPath) === false) {
                    return;
                }

                $this->updateOrCreateProject($driver, $fullPath, $root, $directory);
            });

        $this->cleanupInvalidPaths($directories);

        return Project::count();
    }

    public function getProjects(): Collection
    {
        return Project::all();
    }

    public function getAvailableProjects(): Collection
    {
        return Project::where('driver', '!=', DriverEngine::DRIVER_UNKNOWN)->get();
    }

    public function getUnknownProjects(): Collection
    {
        return Project::where('driver', DriverEngine::DRIVER_UNKNOWN)->get();
    }

    protected function getDirectoryRootPath(?string $driver, string $directory): array
    {
        if ($driver === null) {
            return [$directory, null];
        }

        if ($driver === DriverEngine::DRIVER_UNKNOWN) {
            return [
                $directory,
                '/var/www-default/' . ltrim(config('pages.unknown-driver'), 'pages/'),
            ];
        }

        return [
            $directory,
            '/var/www/' . Str::afterLast($directory, '/') . $this->driverEngine->getDriverByIdentifier(
                $driver
            )->directoryRoot($directory),
        ];
    }

    protected function updateOrCreateProject(
        ?string $driver,
        string $location,
        ?string $path,
        string $directory
    ): Project {
        return Project::updateOrCreate(
            [
                'location' => $location,
            ],
            [
                'driver' => $driver ?? DriverEngine::DRIVER_UNKNOWN,
                'location' => $directory,
                'directory_root' => $path ?? $location,
                'name' => Str::afterLast($directory, '/'),
                'friendly_name' => Str::title(
                    str_replace(['_', '-'], ' ', Str::afterLast($directory, '/'))
                ),
            ]
        );
    }

    protected function cleanupInvalidPaths(array $directories): int
    {
        return (int) Project::whereNotIn('location', $directories)->get()
            ->reduce(function (?int $count, Project $project): int {
                $project->delete();

                return $count + 1;
            });
    }
}

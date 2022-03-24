<?php

namespace App;

use App\Models\Project;
use Illuminate\Support\Facades\File;

class NginxComposer
{
    protected string $stubsPath;

    protected string $sitesPath;

    protected string $dataDirectoryPath;

    protected string $globalConfigurationDirectory;

    protected DriverEngine $driverEngine;

    protected ProjectSupervisor $supervisor;

    public function __construct(DriverEngine $driverEngine)
    {
        $this->stubsPath = base_path('stubs/configs/nginx');
        $this->driverEngine = $driverEngine;
        $this->supervisor = ProjectSupervisor::make();
        $this->dataDirectoryPath = ServDocker::make()->getDataDirectory();
        $this->sitesPath = $this->dataDirectoryPath . 'services/servd/build/sites';
        $this->globalConfigurationDirectory = $this->dataDirectoryPath . 'services/servd/config/nginx/conf.d';
    }

    public static function make(): self
    {
        return new self(app(DriverEngine::class));
    }

    public function configure(): bool
    {
        $this->supervisor->getProjects()->each(function (Project $project): void {
            $configuration = str_replace(
                [
                    '{{$serverName}}',
                    '{{$directoryRoot}}',
                    '{{$driverNginxConfiguration}}',
                    '{{$sslCertificate}}',
                    '{{$sslCertificateKey}}',
                ],
                [
                    strtolower($project->name) . '.test',
                    $project->directory_root,
                    $this->generateDriverNginxConfiguration($project),
                    '/etc/nginx/ssl/' . $project->getCertificateCommonName() . '.crt',
                    '/etc/nginx/ssl/' . $project->getCertificateCommonName() . '.key',
                ],
                $this->getConfigurationStub($project)
            );

            File::put($this->sitesPath . '/' . strtolower($project->name) . '.conf', $configuration);
        });

        $this->copySystemPagesDirectory();

        $this->updateProjectIndexPage();

        return true;
    }

    private function generateDriverNginxConfiguration(Project $project): string
    {
        return $this->driverEngine->getDriverByIdentifier($project->driver)
            ->nginxConfiguration($this->dataDirectoryPath, $this->globalConfigurationDirectory);
    }

    private function getConfigurationStub(Project $project): string
    {
        if ($project->isSecure()) {
            return file_get_contents($this->stubsPath . '/https.conf.stub');
        }

        return file_get_contents($this->stubsPath . '/http.conf.stub');
    }

    private function copySystemPagesDirectory(): void
    {
        File::copyDirectory(
            $this->stubsPath . '/pages',
            $this->dataDirectoryPath . 'pages',
        );
    }

    private function updateProjectIndexPage(): void
    {
        $page = str_replace(
            [
                '{{$availableProjects}}',
                '{{$unknownProjects}}',
            ],
            [
                view('project-index.available', ['projects' => $this->supervisor->getAvailableProjects()])->render(),
                view('project-index.unknown', ['projects' => $this->supervisor->getUnknownProjects()])->render(),
            ],
            file_get_contents($this->dataDirectoryPath . 'pages/project-index/index.php')
        );

        File::put($this->dataDirectoryPath . 'pages/project-index/index.php', $page);
    }
}

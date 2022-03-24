<?php

namespace App\Commands;

use App\DockerComposer;
use App\Models\Service;
use App\NginxComposer;
use App\ProjectSupervisor;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Configure extends Command
{
    /** @var string */
    protected $signature = 'configure';

    /** @var string */
    protected $description = 'Configures the docker compose and services files based upon existing settings';

    protected ProjectSupervisor $supervisor;

    protected DockerComposer $composer;

    protected ServDocker $servd;

    protected NginxComposer $nginxComposer;

    public function __construct(
        ProjectSupervisor $supervisor,
        DockerComposer $composer,
        ServDocker $servd,
        NginxComposer $nginxComposer
    ) {
        $this->supervisor = $supervisor;
        $this->composer = $composer;
        $this->servd = $servd;
        $this->nginxComposer = $nginxComposer;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->task('Scanning working directory for projects', function (): bool {
            return (bool) $this->supervisor->scan();
        });

        $this->task('Rebuilding Docker files', function (): bool {
            $this->servd->setupDockerServiceFiles($this->servd->getDataDirectory());
            $this->composer->setServices(
                Service::enabled()->where('type', '!=', Service::TYPE_CORE)->get()
            );

            return (bool) $this->composer->buildDockerCompose();
        });

        $this->task('Rebuilding project configurations', function (): bool {
            return $this->nginxComposer->configure();
        });
    }
}

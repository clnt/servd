<?php

namespace App\Commands;

use App\CertificateStore;
use App\Concerns\ConsoleHelpers;
use App\Models\Project;
use App\NginxComposer;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Secure extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'secure {project=current} {--u|update}';

    protected ServDocker $servd;

    protected NginxComposer $nginxComposer;

    protected CertificateStore $store;

    /** @var string */
    protected $description = 'Sets the current or given project as secure and generates a certificate if required';

    public function __construct(ServDocker $servd, NginxComposer $nginxComposer)
    {
        $this->servd = $servd;
        $this->nginxComposer = $nginxComposer;
        $this->store = CertificateStore::make();

        parent::__construct();
    }

    public function handle(): void
    {
        $project = $this->getArgument('project') ?? 'current';

        $project = $project !== 'current' ? Project::getByName($project) : Project::getByName(basename(getcwd()));

        if ($project === null) {
            $this->errorMessage(
                'The given project does not exist. If recently added run the configure command first'
            );

            return;
        }

        if ($project->isSecure()) {
            $this->errorMessage('The given project is already set as secure, run the unsecure command to revert');

            return;
        }

        if ($this->store->projectHasValidCertificate($project)) {
            $this->successMessage(
                'The given project already has a valid certificate, reconfiguring and restarting services'
            );

            $project->update(['secure' => true]);

            $this->configureAndRestartServices();

            return;
        }

        $this->store->generate($project);

        $project->update(['secure' => true]);

        $this->configureAndRestartServices();

        $this->successMessage('Project certificate generated, reconfiguring and restarting services');
    }

    private function configureAndRestartServices(): void
    {
        $this->servd->configure();
        $this->nginxComposer->configure();
        $this->servd->restart('servd_core');
    }
}

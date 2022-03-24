<?php

namespace App\Commands;

use App\CertificateStore;
use App\Concerns\ConsoleHelpers;
use App\Models\Project;
use App\NginxComposer;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Unsecure extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'unsecure {project=current}';

    protected ServDocker $servd;

    protected NginxComposer $nginxComposer;

    protected CertificateStore $store;

    /** @var string */
    protected $description = 'Sets the current or given project as non secure and deletes existing certificate files';

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

        if ($project->isSecure() === false) {
            $this->errorMessage('The given project is already set as non secure, run the secure command to use https');

            return;
        }

        $this->store->remove($project);

        $project->update(['secure' => false]);

        $this->configureAndRestartServices();

        $this->successMessage(
            'Project set to non secure and certificate deleted, reconfiguring and restarting services'
        );
    }

    private function configureAndRestartServices(): void
    {
        $this->servd->configure();
        $this->nginxComposer->configure();
        $this->servd->restart('servd_core');
    }
}

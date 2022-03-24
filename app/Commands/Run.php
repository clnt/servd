<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Run extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'run {argument} {container=servd_core}';

    protected ServDocker $servd;

    protected ?string $container;

    /** @var string */
    protected $description = 'Runs the given command in the project directory of the core container';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->container = $this->getArgument('container');

        $this->task('Running command in ' . $this->container . ' container', function (): bool {
            $this->servd->run($this->getArgument('argument'), $this->container);

            return true;
        });
    }
}

<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Cli extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'cli {container=servd_core}';

    protected ServDocker $servd;

    /** @var string */
    protected $description = 'Runs the given command in the project directory of the core container';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $container = $this->getArgument('container') ?? 'servd_core';

        $this->info('Opening interactive shell into the ' . $container . ' container');

        $this->servd->cli($container);
    }
}

<?php

namespace App\Commands;

use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Scan extends Command
{
    /** @var string */
    protected $signature = 'scan';

    /** @var string */
    protected $description = 'Scans the working directory for new projects and restarts containers';

    protected ServDocker $servd;

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->call('configure');

        $this->servd->stop();
        $this->servd->start();
    }
}

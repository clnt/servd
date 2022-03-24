<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Start extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'start {service?}';

    protected ServDocker $servd;

    protected ?string $service;

    /** @var string */
    protected $description = 'Starts the configured or given docker service';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->task('Starting the configured services', function (): bool {
            $this->servd->start($this->getArgument());

            return true;
        });
    }
}

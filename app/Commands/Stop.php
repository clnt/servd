<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Stop extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'stop {service?}';

    protected ServDocker $servd;

    protected ?string $service;

    /** @var string */
    protected $description = 'Stops the configured or given docker service';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->task('Stopping the configured services', function (): bool {
            $this->servd->stop($this->getArgument());

            return true;
        });
    }
}

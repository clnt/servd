<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class Restart extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'restart {service?} {--b|rebuild}';

    protected ServDocker $servd;

    protected ?string $service;

    /** @var string */
    protected $description = 'Restarts the configured docker services with optional rebuild';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->service = $this->getArgument();

        if ($this->option('rebuild')) {
            $this->task('Rebuilding the configured services', function (): bool {
                $this->servd->build($this->service);

                return true;
            });
        }

        $this->task('Restarting the configured services', function (): bool {
            $this->servd->restart($this->service);

            return true;
        });
    }
}

<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

class Rebuild extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'rebuild {service?} {--u|update}';

    protected ServDocker $servd;

    protected ?string $service;

    /** @var string */
    protected $description = 'Rebuilds the configured docker services with optional update';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->service = $this->getArgument();

        if ($this->option('update')) {
            $this->task('Updating the configured services', function (): bool {
                $this->servd->update($this->service);

                return true;
            });
        }

        $this->task('Rebuilding the configured services', function (): bool {
            $this->servd->build($this->service);

            return true;
        });

        if ($this->confirm('Services rebuilt, would you like to restart containers now?', 'yes')) { //phpcs:ignore
            Artisan::call('restart', ['service' => $this->service]);
        }
    }
}

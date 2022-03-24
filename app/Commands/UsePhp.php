<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\Models\Service;
use App\Models\Setting;
use App\ServDocker;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

class UsePhp extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'use {version}';

    protected ServDocker $servd;

    protected ?string $version = null;

    protected string $service = 'servd';

    /** @var string */
    protected $description = 'Sets the given PHP version and rebuilds container';

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->version = $this->getArgument('version');

        if (in_array((string) $this->version, array_values(Service::$phpVersions), true) === false) {
            $this->errorMessage('The given PHP version ' . $this->version . ' is not supported');
            return;
        }

        $this->task('Updating PHP version setting', function (): bool {
            return (bool) Setting::updateValueByKey(Setting::KEY_PHP_VERSION, $this->version);
        });

        Artisan::call('configure');

        $this->task('Rebuilding service', function (): bool {
            $this->servd->build('servd');

            return true;
        });

        if ($this->confirm('Service rebuilt, would you like to restart containers now?', 'yes')) { //phpcs:ignore
            Artisan::call('restart');
        }
    }
}

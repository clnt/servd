<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\Models\Setting;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class SetTimezone extends Command
{
    use ConsoleHelpers;

    protected $signature = 'set:timezone';

    protected $description = 'Updates the timezone setting';

    protected ServDocker $servd;

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;

        parent::__construct();
    }

    public function handle(): void
    {
        $timezone = $this->ask(
            'What is your timezone? This must be a valid IANA timezone i.e. Europe/London',
            'UTC'
        );

        Setting::updateOrCreateValue(['key' => Setting::KEY_TIMEZONE], ['value' => $timezone]);

        $this->successMessage('Updated timezone setting');

        try {
            $this->servd->configure();

            $this->successMessage('Updated docker files');
        } catch (Throwable $exception) {
            $this->errorMessage('An error occurred updating the docker files');
            return;
        }

        $this->info('Run the rebuild command for changes to take effect');
    }
}

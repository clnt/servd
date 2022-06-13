<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\Models\Setting;
use LaravelZero\Framework\Commands\Command;

class DrushUninstall extends Command
{
    use ConsoleHelpers;

    protected $signature = 'drush:uninstall';

    protected $description = 'Uninstall drush in container';

    public function handle(): void
    {
        Setting::where('key', Setting::KEY_DRUSH_VERSION)->delete();

        $this->successMessage('Drush uninstalled successfully, change will take effect on next rebuild');
    }
}

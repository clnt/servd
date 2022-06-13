<?php

namespace App\Commands;

use App\Concerns\ConsoleHelpers;
use App\Models\Setting;
use LaravelZero\Framework\Commands\Command;

class DrushInstall extends Command
{
    use ConsoleHelpers;

    protected $signature = 'drush:install';

    protected $description = 'Install drush in container';

    protected array $availableVersions = [
        '11' => '11',
        '10' => '10',
    ];

    public function handle(): void
    {
        $drushVersion = $this->choice(
            'Which version of Drush would you like to use?',
            $this->availableVersions
        );

        Setting::updateOrCreateValue(['key' => Setting::KEY_DRUSH_VERSION], ['value' => $drushVersion]);

        $this->successMessage('Drush version ' . $drushVersion . ' selected successfully');
    }
}

<?php

namespace App\Commands;

use App\Commands\Exceptions\ErrorSettingWorkingDirectory;
use App\Concerns\ConsoleHelpers;
use App\Models\Service;
use App\Models\Setting;
use App\NginxComposer;
use App\ProjectSupervisor;
use App\ServDocker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class Install extends Command
{
    use ConsoleHelpers;

    /** @var string */
    protected $signature = 'install';

    /** @var string */
    protected $description = 'Installs initial servd services and files';

    protected ServDocker $servd;

    protected ProjectSupervisor $supervisor;

    protected NginxComposer $nginxComposer;

    protected string $dataDirectory;

    protected string $databaseSoftware;

    protected string $databaseVersion;

    protected string $phpVersion;

    protected string $nodeVersion;

    protected string $composerVersion;

    public function __construct(ServDocker $servd, ProjectSupervisor $supervisor, NginxComposer $nginxComposer)
    {
        $this->servd = $servd;
        $this->supervisor = $supervisor;
        $this->nginxComposer = $nginxComposer;

        parent::__construct();
    }

    public function handle(): void
    {
        if (ucwords($this->choice('Are you sure you wish to install ServD?', ['No', 'Yes'], 'Yes')) !== 'Yes') {
            $this->info('Aborting ServD installation');
            return;
        }

        $this->dataDirectory = $this->servd->updateDataDirectoryPath();

        // Setup SQLite database
        $this->setupSqliteDatabase();
        $this->call('migrate', ['--force' => true]);

        $this->servd->persistDataDirectoryPath();

        // Setup pre-defined application services in database
        Service::setupPredefinedServices();

        // Enable the Redis service in database
        $this->enableRedisService();

        // Select NodeJS verson
        $this->selectNodeVersion();

        // Select PHP Version
        $this->selectPhpVersion();

        // Select Composer Version
        $this->selectComposerVersion();

        // Select Database Software (MariaDB, MySQL, Postgresql)
        $this->selectDatabaseSoftware();

        // Select Database Software Version
        $this->selectDatabaseVersion();

        // Install Mailhog Option
        $this->installMailhog();

        // Install Elasticsearch Option
        $this->installElasticsearch();

        // Set working directory / park or set directory
        $this->setWorkingDirectory();

        // Scan projects
        $this->task('Scanning working directory for projects', function (): bool {
            return (bool) $this->supervisor->scan();
        });

        $this->task('Building configuration files', function (): bool {
            // Build directories and dockerfiles
            $this->servd->configure();

            // Build nginx configurations for projects
            return $this->nginxComposer->configure();
        });
    }

    private function setupSqliteDatabase(): void
    {
        $this->info('Checking for an existing database');

        if (File::exists($this->servd->getDataDirectory() . '/database.sqlite')) {
            $this->successMessage('Database already exists.');

            return;
        }

        $this->info('Creating database.');

        if (File::exists($this->servd->getDataDirectory()) === false) {
            File::makeDirectory($this->servd->getDataDirectory());
        }

        File::put($this->servd->getDataDirectory() . 'database.sqlite', '');

        $this->successMessage('Database created successfully');
    }

    private function enableRedisService(): void
    {
        Service::where('service_name', 'redis')
            ->firstOrFail()
            ->update([
                'enabled' => true,
            ]);
    }

    private function selectDatabaseSoftware(): void
    {
        $this->databaseSoftware = $this->choice(
            'Which database software would you like to use?',
            Service::getServiceChoices(Service::TYPE_DATABASE),
        );
    }

    private function selectDatabaseVersion(): void
    {
        $this->databaseVersion = $this->choice(
            'Which version of ' . $this->databaseSoftware . ' would you like to use?',
            Service::getAvailableVersions(strtolower($this->databaseSoftware)),
        );

        Service::whereIn('service_name', ['mysql', 'mariadb'])->update(['enabled' => false]);

        Service::where('service_name', $this->databaseSoftware)
            ->firstOrFail()
            ->update([
                'version' => $this->databaseVersion,
                'enabled' => true,
            ]);

        $this->successMessage(
            'Default database preference ' . $this->databaseSoftware . ' ' . $this->databaseVersion .
            ' successfully chosen'
        );
    }

    private function selectNodeVersion(): void
    {
        $this->nodeVersion = $this->choice(
            'Which Node.js version would you like to use?',
            Service::getNodeVersionChoices(),
            '16'
        );

        Setting::create([
            'key' => Setting::KEY_NODE_VERSION,
            'value' => Arr::get(Service::$nodeVersions, $this->nodeVersion),
        ]);

        $this->successMessage('Node.js version ' . $this->nodeVersion . ' selected');
    }

    private function selectPhpVersion(): void
    {
        $this->phpVersion = $this->choice(
            'Which PHP version would you like to use?',
            Service::getPhpVersionChoices(),
            '8.0'
        );

        Setting::updateOrCreate(['key' => Setting::KEY_PHP_VERSION], ['value' => $this->phpVersion]);

        Service::updateOrCreate(
            [
                'service_name' => 'servd',
            ],
            [
                'version' => $this->phpVersion,
                'enabled' => true,
            ]
        );

        $this->successMessage('PHP version ' . $this->phpVersion . ' selected');
    }

    private function selectComposerVersion(): void
    {
        $this->composerVersion = $this->choice(
            'Which Composer version would you like to use?',
            Service::getComposerVersionChoices(),
            '2'
        );

        Setting::updateOrCreate(
            ['key' => Setting::KEY_COMPOSER_VERSION],
            ['value' => Arr::get(Service::$composerVersions, $this->composerVersion)]
        );

        $this->successMessage('Composer version ' . $this->composerVersion . ' selected');
    }

    private function setWorkingDirectory(): void
    {
        $this->task('Set working directory', function (): bool {
            $existing = $this->supervisor->getWorkingDirectory();
            $question = 'What is the location of your working directory? Select '
                . 'the current directory detected below or select '
                . '\'Other\' to set the path manually';
            $options = collect(getcwd());

            if ($existing !== null) {
                $question = 'What is the location of your working directory? '
                    . 'An existing working directory has been detected, select '
                    . 'the correct directory from the options below or select '
                    . '\'Other\' to set the path manually';
                $options->push($existing);
            }

            $options->push('Other');

            $path = $this->choice($question, $options->toArray());

            if ($path !== 'Other') {
                $this->supervisor->setWorkingDirectory($path);

                $this->info('Working directory set successfully');

                return true;
            }

            $this->supervisor->setWorkingDirectory(
                $this->getManuallySpecifiedWorkingDirectory()
            );

            $this->info('Working directory set successfully');

            return true;
        });
    }

    private function getManuallySpecifiedWorkingDirectory(): string
    {
        $path = $this->ask('Enter the path to your working directory');
        $maxAttempts = 5;
        $currentAttempts = 0;

        while (is_dir($path ?? '') === false && $currentAttempts < $maxAttempts) {
            $this->errorMessage(
                'The directory specified is invalid, check the path and '
                . 'permissions are correct before trying again.'
            );

            $currentAttempts++; // phpcs:ignore

            $path = $this->ask('Enter the path to your working directory');
        }

        throw_if(is_dir($path ?? '') === false, ErrorSettingWorkingDirectory::class);

        return $path;
    }

    private function installMailhog(): void
    {
        if (ucwords($this->choice('Would you like to enable Mailhog?', ['No', 'Yes'], 'Yes')) !== 'Yes') {
            return;
        }

        Service::where('service_name', 'mailhog')
            ->firstOrFail()
            ->update([
                'enabled' => true,
            ]);

        $this->successMessage('Mailhog enabled successfully');
    }

    private function installElasticsearch(): void
    {
        if (ucwords($this->choice('Would you like to install Elasticsearch?', ['No', 'Yes'], 'No')) !== 'Yes') {
            return;
        }

        $version = $this->choice(
            'Which version of Elasticsearch would you like to install?',
            Service::getAvailableVersions('elasticsearch'),
            'latest'
        );

        Service::where('service_name', 'elasticsearch')
            ->firstOrFail()
            ->update([
                'version' => $version,
                'enabled' => true,
            ]);

        $this->successMessage("Elasticsearch {$version} enabled successfully");
    }
}

<?php

namespace Tests;

use App\Console\Cli;
use App\Models\Service;
use App\Models\Setting;
use App\ServDocker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;
use Mockery;
use Mockery\MockInterface;

class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->defineUnixEnvironment();

        parent::setUp();

        if (File::exists(base_path('/tests/Fake/UserHomeDirectory/.servd/database.sqlite'))) {
            return;
        }

        File::put(base_path('/tests/Fake/UserHomeDirectory/.servd/database.sqlite'), '');
    }

    protected function defineUnixEnvironment(): void
    {
        $_SERVER['HOME'] = dirname(__DIR__) . '/tests/Fake/UserHomeDirectory';

        unset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']);
    }

    protected function defineWindowsEnvironment(): void
    {
        ServDocker::make()->resetPlatformDetection();

        $_SERVER['HOME'] = null;
        $_SERVER['HOMEDRIVE'] = 'C:\\\\';
        $_SERVER['HOMEPATH'] = 'Users\\testuser\\tests\\Fake\\UserHomeDirectory';
    }

    protected function fakeDataDirectoryPath(?bool $fullPath = null): string
    {
        if ($fullPath) {
            $path = base_path('tests/Fake/UserHomeDirectory/.servd/');
            Cache::put(Setting::KEY_DATA_DIRECTORY, $path);

            return $path;
        }

        $path = 'tests/Fake/UserHomeDirectory/.servd/';
        Cache::put(Setting::KEY_DATA_DIRECTORY, $path);

        return $path;
    }

    protected function mockCli(): MockInterface
    {
        return tap(Mockery::mock(Cli::class), function (MockInterface $mock): void {
            $this->app->instance(Cli::class, $mock);
        });
    }

    protected function setupDefaultSettingsAndServices(): void
    {
        Setting::factory()->create(['key' => Setting::KEY_PHP_VERSION, 'value' => '8.1']);
        Setting::factory()->create([
            'key' => Setting::KEY_WORKING_DIRECTORY,
            'value' => getcwd(),
        ]);
        Setting::factory()->create([
            'key' => Setting::KEY_DATA_DIRECTORY,
            'value' => $this->fakeDataDirectoryPath(true),
        ]);
        Service::factory()->create([
            'enabled' => true,
            'type' => Service::TYPE_CORE,
            'service_name' => 'servd',
            'name' => 'ServD',
            'version' => '8.1',
            'has_volume' => false,
            'should_build' => true,
        ]);
        Service::factory()->create([
            'enabled' => true,
            'type' => Service::TYPE_DATABASE,
            'service_name' => 'mariadb',
            'name' => 'MariaDB',
            'version' => '10.5',
            'has_volume' => true,
            'should_build' => false,
        ]);
        Service::factory()->create([
            'enabled' => true,
            'type' => Service::TYPE_OTHER,
            'service_name' => 'mailhog',
            'name' => 'Mailhog',
            'version' => 'latest',
            'has_volume' => false,
            'should_build' => false,
        ]);
    }

    protected function recreateFakeCertificateFiles(): void
    {
        file_put_contents(
            $this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.crt',
            ''
        );
        file_put_contents(
            $this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.key',
            ''
        );
        file_put_contents(
            $this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.csr',
            ''
        );
    }
}

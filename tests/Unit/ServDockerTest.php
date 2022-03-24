<?php

namespace Unit;

use App\Drivers\Exceptions\NoServicesEnabled;
use App\Models\Service;
use App\Models\Setting;
use App\ServDocker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServDockerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_update_the_data_directory_path(): void
    {
        $this->assertEquals(
            $this->fakeDataDirectoryPath(true),
            ServDocker::make()->updateDataDirectoryPath()
        );
    }

    /** @test */
    public function it_can_get_the_expected_data_directory_path_for_windows(): void
    {
        $this->defineWindowsEnvironment();

        $this->assertEquals(
            'C:\\\\Users\\testuser\\tests\\Fake\\UserHomeDirectory\\.servd\\',
            ServDocker::make()->updateDataDirectoryPath()
        );
    }

    /** @test */
    public function it_can_persist_the_data_directory_from_cache(): void
    {
        $this->assertSame(
            ServDocker::make()->persistDataDirectoryPath(),
            Setting::where('key', Setting::KEY_DATA_DIRECTORY)->first()->value
        );
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_unix_before_setting_data_directory(): void
    {
        $this->assertTrue(ServDocker::make()->isUnix());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_unix_after_setting_data_directory(): void
    {
        $servd = ServDocker::make();
        $servd->updateDataDirectoryPath();

        $this->assertEquals(0, Setting::count());
        $this->assertTrue(ServDocker::make()->isUnix());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_unix_after_persisting_the_data_directory(): void
    {
        $servd = ServDocker::make();
        $servd->persistDataDirectoryPath();

        $this->assertTrue(ServDocker::make()->isUnix());
    }

    /** @test */
    public function it_returns_false_on_unix_if_windows_is_already_detected_after_persisting_the_data_directory(): void
    {
        Cache::forget(Setting::KEY_DATA_DIRECTORY);
        $this->defineWindowsEnvironment();

        $servd = ServDocker::make();
        $servd->persistDataDirectoryPath();

        $this->assertFalse($servd->isUnix());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_unix_without_persisting_data_directory(): void
    {
        $this->defineUnixEnvironment();
        $servd = ServDocker::make();

        $this->assertTrue($servd->isUnix());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_windows_before_setting_data_directory(): void
    {
        $this->defineWindowsEnvironment();

        $this->assertTrue(ServDocker::make()->isWindows());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_windows_after_setting_data_directory(): void
    {
        $this->defineWindowsEnvironment();

        $servd = ServDocker::make();
        $servd->updateDataDirectoryPath();

        $this->assertEquals(0, Setting::count());
        $this->assertTrue(ServDocker::make()->isWindows());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_on_windows_after_persisting_the_data_directory(): void
    {
        $this->defineWindowsEnvironment();

        $servd = ServDocker::make();
        $servd->persistDataDirectoryPath();

        $this->assertTrue(ServDocker::make()->isWindows());
    }

    /** @test */
    public function it_returns_false_on_windows_if_unix_already_detected_after_persisting_the_data_directory(): void
    {
        $this->defineUnixEnvironment();
        $servd = ServDocker::make();
        $servd->persistDataDirectoryPath();

        $this->assertFalse($servd->isWindows());
    }

    /** @test */
    public function it_can_determine_if_the_system_is_running_windows_without_persisting_data_directory(): void
    {
        $this->defineWindowsEnvironment();
        $servd = ServDocker::make();

        $this->assertTrue($servd->isWindows());
    }

    /** @test */
    public function it_doesnt_set_null_data_directory_value_in_cache_if_not_found_in_database(): void
    {
        Cache::forget(Setting::KEY_DATA_DIRECTORY);
        $servd = ServDocker::make();

        $this->assertNull($servd->getDataDirectory());
        $this->assertFalse(Cache::has(Setting::KEY_DATA_DIRECTORY));
    }

    /** @test */
    public function it_stores_data_directory_value_in_cache_if_not_present_and_found_in_database(): void
    {
        Cache::forget(Setting::KEY_DATA_DIRECTORY);
        Setting::factory()->create(['key' => Setting::KEY_DATA_DIRECTORY, 'value' => 'test']);
        $servd = ServDocker::make();

        $this->assertEquals('test', $servd->getDataDirectory());
        $this->assertEquals('test', Cache::get(Setting::KEY_DATA_DIRECTORY));
    }

    /** @test */
    public function it_throws_an_exception_if_no_services_are_enabled_when_trying_to_build(): void
    {
        Service::factory()->create(['enabled' => false]);
        Service::factory()->create(['enabled' => false]);

        try {
            ServDocker::make()->configure();
        } catch (NoServicesEnabled $exception) {
            $this->assertEquals('No services have been enabled, unable to continue', $exception->getMessage());
            return;
        }

        $this->fail('NoServicesEnabled exception not thrown');
    }

    /** @test */
    public function it_can_start_the_services(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('up -d --remove-orphans', ServDocker::make()->start()->getCommand());
    }

    /** @test */
    public function it_can_start_the_services_and_force_recreate_all_containers(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals(
            'up -d --force-recreate --remove-orphans',
            ServDocker::make()->start(null, true)->getCommand()
        );
    }

    /** @test */
    public function it_can_start_a_specific_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals(
            'start redis',
            ServDocker::make()->start('redis')->getCommand()
        );
    }

    /** @test */
    public function it_can_stop_the_services(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('down', ServDocker::make()->stop()->getCommand());
    }

    /** @test */
    public function it_can_stop_a_specific_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals(
            'stop redis',
            ServDocker::make()->stop('redis')->getCommand()
        );
    }

    /** @test */
    public function it_can_restart_the_services(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('restart', ServDocker::make()->restart()->getCommand());
    }

    /** @test */
    public function it_can_restart_a_specific_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals(
            'restart redis',
            ServDocker::make()->restart('redis')->getCommand()
        );
    }

    /** @test */
    public function it_can_rebuild_the_services(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('build', ServDocker::make()->build()->getCommand());
    }

    /** @test */
    public function it_can_rebuild_a_specific_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('build redis', ServDocker::make()->build('redis')->getCommand());
    }

    /** @test */
    public function it_can_update_the_services(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('pull', ServDocker::make()->update()->getCommand());
    }

    /** @test */
    public function it_can_update_a_specific_service(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->assertEquals('pull redis', ServDocker::make()->update('redis')->getCommand());
    }

    /** @test */
    public function it_can_run_the_given_command_in_container(): void
    {
        $this->mockCli()->shouldReceive('execRealTime')
            ->with('docker exec -w /var/www/' . basename(getcwd()) . ' servd_core php -v')
            ->once();

        $this->assertEquals(
            'docker exec -w /var/www/' . basename(getcwd()) . ' servd_core php -v',
            ServDocker::make()->run('php -v', 'servd_core')->prepare()
        );
    }

    /** @test */
    public function it_can_open_an_interactive_shell_via_cli_method(): void
    {
        $this->mockCli()->shouldReceive('passthrough')
            ->with('docker exec -itw /var/www/' . basename(getcwd()) . ' servd_core /bin/sh')
            ->once();

        $this->assertEquals(
            'docker exec -itw /var/www/' . basename(getcwd()) . ' servd_core /bin/sh',
            ServDocker::make()->cli()->prepare()
        );
    }
}

<?php

namespace Feature\Commands;

use App\Commands\Exceptions\ErrorSettingWorkingDirectory;
use App\Models\Service;
use App\Models\Setting;
use App\ProjectSupervisor;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_install_command(): void
    {
        $this->servdInstallSetup()
            ->expectsQuestion(
                'What is the location of your working directory? Select '
                . 'the current directory detected below or select '
                . '\'Other\' to set the path manually',
                getcwd()
            )->expectsOutput('Working directory set successfully')
            ->expectsOutput('Scanning working directory for projects: ✔')
            ->expectsOutput('Building configuration files: ✔')
            ->assertSuccessful();

        $this->assertEquals('7.4', Setting::where('key', Setting::KEY_PHP_VERSION)->firstOrFail()->value);
        $this->assertEquals(
            1,
            Service::where('service_name', 'redis')->firstOrFail()->enabled
        );
        $this->assertEquals(
            1,
            Service::where('service_name', 'servd')->where('version', '7.4')->firstOrFail()->enabled
        );
        $this->assertEquals(
            1,
            Service::where('service_name', 'mysql')->where('version', '5.7')->firstOrFail()->enabled
        );
        $this->assertEquals(getcwd(), Setting::where('key', Setting::KEY_WORKING_DIRECTORY)->firstOrFail()->value);
    }

    /** @test */
    public function it_can_run_the_install_command_and_choose_existing_working_directory(): void
    {
        $supervisor = app(ProjectSupervisor::class);
        $directory = base_path('tests/Support/Projects');
        $supervisor->setWorkingDirectory($directory);

        $this->servdInstallSetup()
            ->expectsQuestion(
                'What is the location of your working directory? '
                . 'An existing working directory has been detected, select '
                . 'the correct directory from the options below or select '
                . '\'Other\' to set the path manually',
                $directory
            )->expectsOutput('Working directory set successfully');

        $this->assertEquals($directory, $supervisor->getWorkingDirectory());
    }

    /** @test */
    public function it_can_run_the_install_command_and_manually_specify_a_working_directory(): void
    {
        $supervisor = app(ProjectSupervisor::class);
        $directory = base_path('tests/Support/Projects');

        $this->servdInstallSetup()
            ->expectsQuestion(
                'What is the location of your working directory? Select '
                . 'the current directory detected below or select '
                . '\'Other\' to set the path manually',
                'Other'
            )->expectsQuestion('Enter the path to your working directory', $directory)
            ->expectsOutput('Working directory set successfully');

        $this->assertEquals($directory, $supervisor->getWorkingDirectory());
    }

    /** @test */
    public function can_run_the_install_command_and_manually_specify_a_working_dir_allowing_max_attempts(): void
    {
        $supervisor = app(ProjectSupervisor::class);
        $directory = base_path('tests/Support/Projects');

        $this->servdInstallSetup()
            ->expectsQuestion(
                'What is the location of your working directory? Select '
                . 'the current directory detected below or select '
                . '\'Other\' to set the path manually',
                'Other'
            )->expectsQuestion('Enter the path to your working directory', '/invalid-path')
            ->expectsOutput(
                'The directory specified is invalid, check the path and '
                . 'permissions are correct before trying again. ✖'
            )
            ->expectsQuestion('Enter the path to your working directory', '/invalid-path')
            ->expectsOutput(
                'The directory specified is invalid, check the path and '
                . 'permissions are correct before trying again. ✖'
            )
            ->expectsQuestion('Enter the path to your working directory', $directory)
            ->expectsOutput('Working directory set successfully');

        $this->assertEquals($directory, $supervisor->getWorkingDirectory());
    }

    /** @test */
    public function can_run_the_install_command_and_manually_specify_a_invalid_working_dir_exception(): void
    {
        try {
            $this->servdInstallSetup()
                ->expectsQuestion(
                    'What is the location of your working directory? Select '
                    . 'the current directory detected below or select '
                    . '\'Other\' to set the path manually',
                    'Other'
                )->expectsQuestion('Enter the path to your working directory', '/invalid-path')
                ->expectsOutput(
                    'The directory specified is invalid, check the path and '
                    . 'permissions are correct before trying again. ✖'
                );
        } catch (ErrorSettingWorkingDirectory $exception) {
            $this->assertEquals(
                'Unable to set working directory, max attempts reached.',
                $exception->getMessage()
            );
            return;
        }
    }

    /** @test */
    public function it_aborts_the_installation_if_the_user_selects_no_to_setup(): void
    {
        $this->artisan('install')
            ->expectsQuestion('Are you sure you wish to install ServD?', 'No')
            ->expectsOutput('Aborting ServD installation')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_allows_selection_of_database_software_and_version(): void
    {
        $this->servdDatabaseInstallSetup()
            ->expectsQuestion('Which database software would you like to use?', 'mariadb')
            ->expectsQuestion('Which version of mariadb would you like to use?', '10.5')
            ->expectsOutput('Default database preference mariadb 10.5 successfully chosen ✔')
            ->expectsQuestion('Would you like to enable Mailhog?', 'Yes')
            ->expectsOutput('Mailhog enabled successfully ✔')
            ->expectsQuestion('Would you like to install Elasticsearch?', 'No')
            ->expectsQuestion(
                'What is the location of your working directory? Select '
                . 'the current directory detected below or select '
                . '\'Other\' to set the path manually',
                getcwd()
            )->assertExitCode(0);
    }

    protected function servdInstallSetup(): PendingCommand
    {
        return $this->artisan('install')
            ->expectsQuestion('Are you sure you wish to install ServD?', 'Yes')
            ->expectsOutput('Checking for an existing database')
            ->expectsQuestion('Which Node.js version would you like to use?', '16')
            ->expectsOutput('Node.js version 16 selected ✔')
            ->expectsQuestion('Which PHP version would you like to use?', '7.4')
            ->expectsOutput('PHP version 7.4 selected ✔')
            ->expectsQuestion('Which Composer version would you like to use?', '2')
            ->expectsOutput('Composer version 2 selected ✔')
            ->expectsQuestion('Which database software would you like to use?', 'mysql')
            ->expectsQuestion('Which version of mysql would you like to use?', '5.7')
            ->expectsOutput('Default database preference mysql 5.7 successfully chosen ✔')
            ->expectsQuestion('Would you like to enable Mailhog?', 'Yes')
            ->expectsOutput('Mailhog enabled successfully ✔')
            ->expectsQuestion('Would you like to install Elasticsearch?', 'Yes')
            ->expectsQuestion('Which version of Elasticsearch would you like to install?', '7.0')
            ->expectsOutput('Elasticsearch 7.0 enabled successfully ✔');
    }

    protected function servdDatabaseInstallSetup(): PendingCommand
    {
        return $this->artisan('install')
            ->expectsQuestion('Are you sure you wish to install ServD?', 'Yes')
            ->expectsOutput('Checking for an existing database')
            ->expectsQuestion('Which Node.js version would you like to use?', '16')
            ->expectsOutput('Node.js version 16 selected ✔')
            ->expectsQuestion('Which PHP version would you like to use?', '7.4')
            ->expectsOutput('PHP version 7.4 selected ✔')
            ->expectsQuestion('Which Composer version would you like to use?', '2')
            ->expectsOutput('Composer version 2 selected ✔');
    }
}

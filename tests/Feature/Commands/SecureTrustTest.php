<?php

namespace Tests\Feature\Commands;

use App\CertificateStore;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SecureTrustTest extends TestCase
{
    use DatabaseMigrations;

    protected MockInterface $cli;

    protected string $dataDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cli = $this->mockCli();
        $this->dataDirectory = $this->fakeDataDirectoryPath();
    }

    /** @test */
    public function it_asks_for_confirmation_and_runs_command_if_unix_detected(): void
    {
        $this->cli->shouldReceive('passthrough')->once();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('CA certificate found ✔')
            ->expectsConfirmation(
                'This command requires macOS and elevated privileges, do you wish to continue?',
                'yes'
            );
    }

    /** @test */
    public function it_returns_an_informational_message_if_windows_is_detected(): void
    {
        $this->defineWindowsEnvironment();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('CA certificate found ✔')
            ->expectsOutput('Please refer to the documentation on how to trust the root certificate on Windows');
    }

    /** @test */
    public function it_returns_an_informational_message_if_operating_system_is_not_supported(): void
    {
        $this->defineUnsupportedEnvironment();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('CA certificate found ✔')
            ->expectsOutput('Operating system unsupported, root CA must be trusted manually ✖');
    }

    /** @test */
    public function it_returns_unsupported_message_if_not_running_macos_command(): void
    {
        $this->defineUnixEnvironment();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('CA certificate found ✔')
            ->expectsConfirmation(
                'This command requires macOS and elevated privileges, do you wish to continue?',
                'no',
            )->expectsOutput('Operating system unsupported, root CA must be trusted manually ✖');
    }

    /** @test */
    public function it_generates_the_root_certificate_authority_if_not_present_in_data_directory(): void
    {
        $this->markTestIncomplete('Mocking not working as expected');

        $this->defineWindowsEnvironment();

        $certificateStore = tap(Mockery::mock(CertificateStore::class), function (MockInterface $mock): void {
            $this->app->instance(CertificateStore::class, $mock);
        });

        File::delete($this->dataDirectory . '/certificates/servdCA.crt');

        $certificateStore->shouldReceive('rootCertificateExists')->once();
        $certificateStore->shouldReceive('generateRootCA')->andReturn(true);
        $this->cli->shouldReceive('execRealTime')->twice();

        $this->artisan('secure:trust')
            ->expectsOutput('Checking for CA certificate')
            ->expectsOutput('Generating root CA ✔');

        File::put($this->dataDirectory . '/certificates/servdCA.crt', '');
    }
}

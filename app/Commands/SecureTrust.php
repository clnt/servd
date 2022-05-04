<?php

namespace App\Commands;

use App\CertificateStore;
use App\Concerns\ConsoleHelpers;
use App\ServDocker;
use LaravelZero\Framework\Commands\Command;

class SecureTrust extends Command
{
    use ConsoleHelpers;

    protected $signature = 'secure:trust';

    protected $description = 'Attempts to trust the CA certificate on machine, requires administrator privileges';

    protected ServDocker $servd;

    public function __construct(ServDocker $servd)
    {
        $this->servd = $servd;
        $this->store = CertificateStore::make();

        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Checking for CA certificate');

        if ($this->store->rootCertificateExists()) {
            $this->successMessage('CA certificate found');

            $this->setup();
            return;
        }

        if ($this->task('Generating root CA', fn (): bool => $this->store->generateRootCA())) {
            $this->setup();
        }

        $this->errorMessage('Unable to generate root CA');
    }

    protected function setup(): void
    {
        if ($this->servd->isWindows()) {
            $this->info(
                'Please refer to the documentation on how to trust the root certificate on Windows'
            );
            return;
        }

        if ($this->servd->isUnix() && $this->confirmElevatedPrivileges()) {
            $this->store->trustMacOsCA();
            return;
        }

        $this->errorMessage('Operating system unsupported, root CA must be trusted manually');
    }

    protected function confirmElevatedPrivileges(): bool
    {
        return $this->confirm(
            'This command requires macOS and elevated privileges, do you wish to continue?',
            'yes'
        );
    }
}

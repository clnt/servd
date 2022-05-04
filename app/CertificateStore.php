<?php

namespace App;

use App\Models\Certificate;
use App\Models\Project;
use Illuminate\Support\Facades\File;

class CertificateStore
{
    protected string $container = 'servd_core';

    protected ServDocker $servd;

    protected string $certDirectory;

    protected ?Project $project;

    protected string $commonName;

    protected string $stubsPath;

    public function __construct(ServDocker $servd, ?Project $project = null)
    {
        $this->servd = $servd;
        $this->project = $project;
        $this->certDirectory = $servd->getDataDirectory() . 'certificates';
        $this->stubsPath = base_path('stubs/configs/openssl');
    }

    public static function make(?Project $project = null): self
    {
        return app(__CLASS__, ['servd' => app(ServDocker::class), 'project' => $project]);
    }

    public function getCertificateDirectory(): string
    {
        return $this->certDirectory;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function trustMacOsCA(): void
    {
        $certificate = $this->certDirectory . '/servdCA.crt';

        $this->servd->runHostCommand(
            "sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain {$certificate}"
        );
    }

    public function remove(Project $project): bool
    {
        $this->project = $project;

        $certificate = $this->project->certificate;

        if ($certificate === null) {
            return true;
        }

        File::delete([
            $certificate->path,
            str_replace('.crt', '.key', $certificate->path),
            str_replace('.crt', '.csr', $certificate->path),
        ]);

        return $certificate->delete();
    }

    public function generate(Project $project): bool
    {
        $this->project = $project;
        $this->generateCommonName();

        if ($this->rootCertificateExists() === false) {
            $this->generateRootCA();
        }

        if ($this->generateCsr()) {
            return tap($this->generateCertificate(), fn (): Certificate => Certificate::updateOrCreate(
                [
                    'common_name' => $this->commonName,
                ],
                [
                    'project_id' => $this->project->id,
                    'path' => $this->certDirectory . '/' . $this->commonName . '.crt',
                    'container_path' => '/etc/nginx/ssl/' . $this->commonName . '.crt',
                    'expires' => now()->addDays(1825),
                ]
            ));
        }

        return false;
    }

    public function projectHasValidCertificate(Project $project): bool
    {
        $certificate = $project->certificate;

        if ($certificate === null) {
            return false;
        }

        return $certificate->expires > now() && $certificate->fileExists();
    }

    public function generateRootCA(): bool
    {
        $privateKeyCommand = "openssl genrsa -out /etc/nginx/ssl/servdCA.key 2048";
        $rootCertificateCommand = "openssl req -x509 -newkey rsa:2048 -nodes -keyout /etc/nginx/ssl/servdCA.key "
            . "-days 1825 -out /etc/nginx/ssl/servdCA.crt -subj '/O=ServD Development Environment/C=UK'";

        $this->servd->shell($privateKeyCommand, $this->container);
        $this->servd->shell($rootCertificateCommand, $this->container);

        return $this->rootCertificateExists();
    }

    protected function generateCsr(): bool
    {
        $configurationFileName = $this->generateOpenSslConfiguration();

        $command = "openssl req -new -sha256 -nodes -keyout /etc/nginx/ssl/{$this->commonName}.key "
        . "-subj '/C=UK/ST=ServD Development Environment/O=ServD/OU=Development Environment/CN=*.{$this->commonName}'"
        . " -reqexts SAN "
        . "-config /etc/nginx/ssl/configs/{$configurationFileName} "
        . "-out /etc/nginx/ssl/{$this->commonName}.csr";

        $this->servd->shell($command, $this->container);

        return $this->certificatePrivateKeyExists() && $this->certificateCsrExists();
    }

    protected function generateCertificate(): bool
    {
        $command = "openssl x509 -req "
            . "-extfile <(printf \"subjectAltName=DNS:{$this->commonName},DNS:*.{$this->commonName}\") "
            . "-days 825 -in /etc/nginx/ssl/{$this->commonName}.csr -CA /etc/nginx/ssl/servdCA.crt "
            . "-CAkey /etc/nginx/ssl/servdCA.key -CAcreateserial "
            . "-out /etc/nginx/ssl/{$this->commonName}.crt "
            . "-sha256";

        $this->servd->shell($command, $this->container);

        return $this->certificateExists();
    }

    protected function generateCommonName(): string
    {
        $this->commonName = $this->project->name . '.test';

        return $this->commonName;
    }

    protected function generateOpenSslConfiguration(): string
    {
        if (File::exists($this->certDirectory . '/configs') === false) {
            File::makeDirectory($this->certDirectory . '/configs');
        }

        $configuration = str_replace(
            ['{{$commonName}}'],
            [$this->commonName],
            file_get_contents($this->stubsPath . '/site-config.stub')
        );

        $fileName = $this->commonName . '-openssl.cnf';

        File::put($this->certDirectory . '/configs/' . $fileName, $configuration);

        return $fileName;
    }

    public function rootCertificateExists(): bool
    {
        return File::exists($this->certDirectory . '/servdCA.crt');
    }

    protected function certificatePrivateKeyExists(): bool
    {
        return File::exists($this->certDirectory . '/' . $this->commonName . '.key');
    }

    protected function certificateCsrExists(): bool
    {
        return File::exists($this->certDirectory . '/' . $this->commonName . '.csr');
    }

    protected function certificateExists(): bool
    {
        return File::exists($this->certDirectory . '/' . $this->commonName . '.crt');
    }
}

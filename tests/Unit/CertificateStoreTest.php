<?php

namespace Tests\Unit;

use App\CertificateStore;
use App\Models\Certificate;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CertificateStoreTest extends TestCase
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
    public function it_can_resolve_the_certificate_store_and_get_certificate_directory_path(): void
    {
        $this->assertEquals(
            $this->dataDirectory . 'certificates',
            app(CertificateStore::class)->getCertificateDirectory()
        );
    }

    /** @test */
    public function it_can_resolve_the_certificate_store_via_static_make_method_and_specify_project(): void
    {
        $project = Project::factory()->create(['name' => 'specified-project']);

        $result = CertificateStore::make($project);

        $this->assertEquals($project->name, $result->getProject()->name);
    }

    /** @test */
    public function it_can_generate_a_root_certificate_and_project_certificate_if_they_do_not_exist(): void
    {
        $project = Project::factory()->create(['secure' => true]);
        $commonName = $project->name . '.test';

        $this->assertEquals('example-project.test', $commonName);
        $this->assertTrue($project->secure);

        $this->mockCertificateGeneration($commonName);

        app(CertificateStore::class)->generate($project);
    }

    /** @test */
    public function it_persists_a_record_of_the_certificate_when_generated(): void
    {
        Carbon::setTestNow('2022-03-23 12:00:00');

        $project = Project::factory()->create(['secure' => true]);
        $commonName = $project->name . '.test';

        $this->mockCertificateGeneration($commonName);

        app(CertificateStore::class)->generate($project);

        $this->assertEquals(1, Certificate::count());

        $certificate = Certificate::first();

        $this->assertEquals($project->id, $certificate->project_id);
        $this->assertEquals($commonName, $certificate->common_name);
        $this->assertEquals(
            '/etc/nginx/ssl/' . $certificate->common_name . '.crt',
            $certificate->container_path
        );
        $this->assertEquals(
            $this->dataDirectory . 'certificates/' . $certificate->common_name . '.crt',
            $certificate->path
        );
        $this->assertEquals(now()->addDays(1825), $certificate->expires);
    }

    /** @test */
    public function generate_returns_false_if_no_certificate_is_generated(): void
    {
        $project = Project::factory()->create(['secure' => true]);

        tap(Mockery::mock(CertificateStore::class), function (MockInterface $mock): void {
            $this->app->instance(CertificateStore::class, $mock);
        })->shouldReceive('generate')->with($project)->andReturn(false)->once();

        $this->assertFalse(app(CertificateStore::class)->generate($project));
    }

    /** @test */
    public function it_can_check_if_the_given_project_has_a_valid_certificate(): void
    {
        Carbon::setTestNow('2022-03-23 12:00:00');

        $store = CertificateStore::make();

        $project = Project::factory()->create();
        Certificate::factory()->create();

        $this->assertTrue($store->projectHasValidCertificate($project));
    }

    /** @test */
    public function it_can_check_if_the_given_project_has_an_invalid_certificate(): void
    {
        Carbon::setTestNow('2022-03-23 12:00:00');

        $store = CertificateStore::make();

        $project = Project::factory()->create();

        $this->assertFalse($store->projectHasValidCertificate($project));

        Certificate::factory()->create(['expires' => now()->subDay()]);

        $this->assertFalse($store->projectHasValidCertificate($project));
    }

    /** @test */
    public function it_can_remove_existing_certificates_for_a_project(): void
    {
        $project = Project::factory()->create();
        Certificate::factory()->create();

        $this->assertTrue(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.crt')
        );
        $this->assertTrue(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.key')
        );
        $this->assertTrue(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.csr')
        );
        $this->assertEquals(1, Certificate::count());

        CertificateStore::make()->remove($project);

        $this->assertFalse(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.crt')
        );
        $this->assertFalse(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.key')
        );
        $this->assertFalse(
            File::exists($this->fakeDataDirectoryPath(true) . 'certificates/example-project.test.csr')
        );
        $this->assertEquals(0, Certificate::count());

        $this->recreateFakeCertificateFiles();
    }

    /** @test */
    public function remove_returns_true_if_no_certificate_has_been_persisted_in_database(): void
    {
        $project = Project::factory()->create();

        $this->assertTrue(CertificateStore::make()->remove($project));
    }

    private function mockCertificateGeneration(string $commonName): void
    {
        $workingDirectory = basename(getcwd());

        // Generate Root CA private key.
        $this->cli->shouldReceive('execRealTime')
            ->with("docker exec -w /var/www/" . $workingDirectory . " servd_core openssl genrsa "
            . "-out '/etc/nginx/ssl/servdCA.key' 2048")->once();

        // Generate Root CA certificate.
        $this->cli->shouldReceive('execRealTime')
            ->with("docker exec -w /var/www/" . $workingDirectory . " servd_core openssl req -x509 "
                . "-newkey rsa:2048 -nodes -keyout '/etc/nginx/ssl/servdCA.key' -days 1825 "
            . "-out '/etc/nginx/ssl/servdCA.crt' -subj '/O=ServD Development Environment/C=UK'")->once();

        // Generate Intermediate certificate private key and CSR.
        $this->cli->shouldReceive('execRealTime')
            ->with("docker exec -w /var/www/" . $workingDirectory . " servd_core openssl req -nodes "
                . "-newkey rsa:2048 -keyout '/etc/nginx/ssl/{$commonName}.key' -out '/etc/nginx/ssl/{$commonName}.csr' "
            . "-subj '/CN={$commonName}/O=ServD Development Environment/C=UK'")->once();

        // Generate Intermediate certificate signed by Root CA.
        $this->cli->shouldReceive('execRealTime')
            ->with("docker exec -w /var/www/" . $workingDirectory . " servd_core openssl x509 -req "
                . "-in '/etc/nginx/ssl/{$commonName}.csr' -CA '/etc/nginx/ssl/servdCA.crt' "
                . "-CAkey '/etc/nginx/ssl/servdCA.key' -CAcreateserial "
            . "-out '/etc/nginx/ssl/{$commonName}.crt' -days 1825 -sha256");
    }
}

<?php

namespace Tests\Unit;

use App\Models\Project;
use App\NginxComposer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class NginxComposerTest extends TestCase
{
    use DatabaseMigrations, MatchesSnapshots;

    /** @test */
    public function it_can_instantiate_the_nginx_composer_with_static_make_method(): void
    {
        $this->assertInstanceOf(NginxComposer::class, NginxComposer::make());
    }

    /** @test */
    public function the_default_nginx_configuration_is_as_expected(): void
    {
        $this->assertMatchesTextSnapshot(
            file_get_contents(base_path('stubs/docker/servd/build/config/nginx.conf'))
        );
    }

    /** @test */
    public function it_can_generate_the_http_nginx_configuration_for_the_laravel_driver(): void
    {
        Project::factory()->create([
            'name' => 'example-project',
            'directory_root' => 'tests/Support/Projects/Laravel',
            'secure' => false,
        ]);

        NginxComposer::make()->configure();

        $this->assertConfigurationMatchesSnapshot();
    }

    /** @test */
    public function it_can_generate_the_https_nginx_configuration_for_the_laravel_driver(): void
    {
        Project::factory()->create([
            'name' => 'example-project',
            'directory_root' => 'tests/Support/Projects/Laravel',
            'secure' => true,
        ]);

        NginxComposer::make()->configure();

        $this->assertConfigurationMatchesSnapshot();
    }

    protected function assertConfigurationMatchesSnapshot(): void
    {
        $this->assertMatchesTextSnapshot(
            file_get_contents(
                base_path('tests/Fake/UserHomeDirectory/.servd/services/servd/build/sites/example-project.conf')
            )
        );
    }
}

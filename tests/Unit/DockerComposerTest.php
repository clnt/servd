<?php

namespace Tests\Unit;

use App\DockerComposer;
use App\Models\Service;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DockerComposerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_instantiate_the_docker_composer_with_static_make_method(): void
    {
        $this->assertInstanceOf(DockerComposer::class, DockerComposer::make());
    }

    /** @test */
    public function it_can_set_and_get_given_services(): void
    {
        Service::factory()->create();
        Service::factory()->create();

        $services = Service::all();

        $this->assertEquals(2, $services->count());

        $composer = DockerComposer::make()->setServices($services);

        $this->assertSame($services, $composer->getServices());
    }
}

<?php

namespace Unit\Models;

use App\Models\Certificate;
use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_check_if_a_project_is_secure(): void
    {
        $project = Project::factory(['secure' => false])->create();

        $this->assertFalse($project->isSecure());

        $project->update(['secure' => true]);

        $this->assertTrue($project->fresh()->isSecure());
    }

    /** @test */
    public function it_can_get_the_expected_project_url_when_not_secure(): void
    {
        $project = Project::factory([
            'name' => 'test-non-secure',
            'secure' => false,
        ])->create();

        $this->assertEquals('http://test-non-secure.test', $project->url());
    }

    /** @test */
    public function it_can_get_the_expected_project_url_when_secure(): void
    {
        $project = Project::factory([
            'name' => 'test-secure',
            'secure' => true,
        ])->create();

        $this->assertEquals('https://test-secure.test', $project->url());
    }

    /** @test */
    public function it_can_get_the_related_certificate(): void
    {
        $project = Project::factory()->create();
        $certificate = Certificate::factory()->create(['project_id' => $project->id]);

        $result = $project->certificate;

        $this->assertNotNull($result);
        $this->assertEquals($certificate->id, $result->id);
        $this->assertEquals($project->id, $result->project_id);
    }

    /** @test */
    public function it_can_get_the_certificate_common_name(): void
    {
        $project = Project::factory()->create();

        $this->assertNull($project->getCertificateCommonName());

        Certificate::factory()->create(['project_id' => $project->id]);

        $this->assertEquals('example-project.test', $project->fresh()->getCertificateCommonName());
    }

    /** @test */
    public function it_can_get_project_by_name(): void
    {
        $project = Project::factory(['name' => 'test-name'])->create();

        $this->assertEquals($project->id, Project::getByName($project->name)->id);
    }

    /** @test */
    public function it_returns_null_if_unable_to_find_project_by_name(): void
    {
        $this->assertNull(Project::getByName('invalid-name'));
    }
}

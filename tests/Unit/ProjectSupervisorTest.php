<?php

namespace Tests\Unit;

use App\DriverEngine;
use App\Drivers\Laravel;
use App\Models\Project;
use App\Models\Setting;
use App\ProjectSupervisor;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Support\TestDriver;
use Tests\TestCase;

class ProjectSupervisorTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('drivers', [
            Laravel::class,
            TestDriver::class,
        ]);
    }

    /** @test */
    public function it_can_instantiate_the_supervisor_with_static_make_method(): void
    {
        $this->assertInstanceOf(ProjectSupervisor::class, ProjectSupervisor::make());
    }

    /** @test */
    public function it_can_set_the_working_directory(): void
    {
        $result = ProjectSupervisor::make()->setWorkingDirectory(getcwd());

        $this->assertEquals(getcwd(), $result->value);
        $this->assertEquals(getcwd(), Setting::where('key', Setting::KEY_WORKING_DIRECTORY)->firstOrFail()->value);
    }

    /** @test */
    public function it_can_get_the_working_directory(): void
    {
        ProjectSupervisor::make()->setWorkingDirectory(getcwd());

        $this->assertEquals(getcwd(), ProjectSupervisor::make()->getWorkingDirectory());
    }

    /** @test */
    public function it_can_scan_the_working_directory_for_projects(): void
    {
        $supervisor = ProjectSupervisor::make();
        $supervisor->setWorkingDirectory(base_path('tests/Support/Projects'));

        $result = $supervisor->scan();

        $this->assertEquals(5, $result);
        $this->assertEquals(5, Project::count());
    }

    /** @test */
    public function it_can_scan_the_working_directory_for_projects_and_cleanup_old(): void
    {
        Project::factory()->create([
            'name' => 'invalid-project',
        ]);

        $this->assertEquals(1, Project::count());

        $supervisor = ProjectSupervisor::make();
        $supervisor->setWorkingDirectory(base_path('tests/Support/Projects'));

        $result = $supervisor->scan();

        $this->assertEquals(5, $result);
        $this->assertEquals(5, Project::count());
    }

    /** @test */
    public function it_can_get_all_projects(): void
    {
        Project::factory()->create([
            'name' => 'project-one',
        ]);
        Project::factory()->create([
            'name' => 'project-two',
        ]);

        $supervisor = ProjectSupervisor::make();

        $this->assertEquals(2, $supervisor->getProjects()->count());
        $this->assertEquals(2, Project::count());
    }

    /** @test */
    public function it_can_get_the_available_projects(): void
    {
        Project::factory()->create([
            'name' => 'project-one',
            'driver' => 'laravel',
        ]);
        Project::factory()->create([
            'name' => 'project-two',
            'driver' => 'generic_html',
        ]);

        Project::factory()->create([
            'name' => 'invalid-project',
            'driver' => DriverEngine::DRIVER_UNKNOWN,
        ]);

        $result = ProjectSupervisor::make()->getAvailableProjects();

        $this->assertNotContains(DriverEngine::DRIVER_UNKNOWN, $result->pluck('driver')->toArray());
        $this->assertEquals(2, $result->count());
        $this->assertEquals(3, Project::count());
    }

    /** @test */
    public function it_can_get_the_unknown_projects(): void
    {
        Project::factory()->create([
            'name' => 'project-one',
            'driver' => DriverEngine::DRIVER_UNKNOWN,
        ]);
        Project::factory()->create([
            'name' => 'project-two',
            'driver' => DriverEngine::DRIVER_UNKNOWN,
        ]);

        Project::factory()->create([
            'name' => 'invalid-project',
            'driver' => 'laravel',
        ]);

        $result = ProjectSupervisor::make()->getUnknownProjects();

        $this->assertNotContains('laravel', $result->pluck('driver')->toArray());
        $this->assertEquals(2, $result->count());
        $this->assertEquals(3, Project::count());
    }
}

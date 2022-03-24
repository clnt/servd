<?php

namespace Feature\Commands;

use App\Models\Certificate;
use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UnsecureTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_an_error_message_if_the_current_project_does_not_exist(): void
    {
        $this->artisan('unsecure')
            ->expectsOutput(
                'The given project does not exist. If recently added run the configure command first ✖'
            );
    }

    /** @test */
    public function it_returns_an_error_message_if_the_current_project_is_already_non_secure(): void
    {
        Project::factory(['secure' => false])->create();
        $this->artisan('unsecure example-project')
            ->expectsOutput(
                'The given project is already set as non secure, run the secure command to use https ✖'
            );
    }

    /** @test */
    public function it_can_run_the_servd_unsecure_command(): void
    {
        $this->setupDefaultSettingsAndServices();
        $this->mockCli()->shouldReceive('execRealTime')->once();
        $project = Project::factory()->create(['secure' => true]);
        Certificate::factory()->create();

        $this->assertTrue($project->isSecure());
        $this->assertNotNull($project->certificate);

        $this->artisan('unsecure example-project')
            ->expectsOutput(
                'Project set to non secure and certificate deleted, reconfiguring and restarting services ✔'
            )->assertSuccessful();

        $this->assertFalse($project->fresh()->isSecure());
        $this->assertNull($project->fresh()->certificate);

        $this->recreateFakeCertificateFiles();
    }
}

<?php

namespace Tests\Feature\Commands;

use App\Models\Certificate;
use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SecureTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_an_error_message_if_the_current_project_does_not_exist(): void
    {
        $this->artisan('secure')
            ->expectsOutput(
                'The given project does not exist. If recently added run the configure command first ✖'
            );
    }

    /** @test */
    public function it_returns_an_error_message_if_the_current_project_is_already_secure(): void
    {
        Project::factory(['secure' => true])->create();
        $this->artisan('secure example-project')
            ->expectsOutput(
                'The given project is already set as secure, run the unsecure command to revert ✖'
            );
    }

    /** @test */
    public function it_can_set_a_project_as_secure_and_check_for_existing_valid_certificate(): void
    {
        $this->setupDefaultSettingsAndServices();
        $project = Project::factory()->create();
        Certificate::factory()->create();

        $this->assertFalse($project->isSecure());

        $this->mockCli()->shouldReceive('execRealTime')->once();

        $this->artisan('secure example-project')
            ->expectsOutput(
                'The given project already has a valid certificate, reconfiguring and restarting services ✔'
            );
    }

    /** @test */
    public function it_can_set_a_project_as_secure(): void
    {
        $this->setupDefaultSettingsAndServices();
        $this->mockCli()->shouldReceive('execRealTime')->times(5);
        $project = Project::factory()->create();

        File::delete($this->fakeDataDirectoryPath() . '/certificates/servdCA.crt');

        $this->assertEquals(0, Certificate::count());

        $this->artisan('secure example-project')
            ->expectsOutput('Project certificate generated, reconfiguring and restarting services ✔');

        File::put($this->fakeDataDirectoryPath() . '/certificates/servdCA.crt', '');

        $this->assertEquals(1, Certificate::count());
        $this->assertTrue($project->fresh()->isSecure());
    }
}

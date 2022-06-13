<?php

namespace Feature\Commands;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DrushUninstallTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_can_run_the_drush_uninstall_command(): void
    {
        Setting::factory()->create([
            'key' => Setting::KEY_DRUSH_VERSION,
            'value' => 11,
        ]);

        $this->assertEquals('11', Setting::where('key', Setting::KEY_DRUSH_VERSION)->firstOrFail()->value);

        $this->artisan('drush:uninstall')
            ->expectsOutput('Drush uninstalled successfully, change will take effect on next rebuild ✔')
            ->assertExitCode(0);

        $this->assertNull(Setting::where('key', Setting::KEY_DRUSH_VERSION)->first());
    }
}

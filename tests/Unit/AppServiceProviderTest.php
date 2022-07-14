<?php

namespace Tests\Unit;

use App\Console\Cli;
use App\Providers\AppServiceProvider;
use App\ServDocker;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    /** @test */
    public function it_does_not_set_the_cli_timeout_when_process_timeout_specified_is_not_an_integer(): void
    {
        config()->set('servd.process_timeout', false);

        (new AppServiceProvider($this->app))->boot();

        $this->assertEquals(0, app(Cli::class)->getTimeout());
    }

    /** @test */
    public function it_sets_the_config_database_path_on_boot(): void
    {
        config()->set('database.connections.sqlite.database', 'invalid-path');

        $this->assertEquals(config('database.connections.sqlite.database'), 'invalid-path');

        (new AppServiceProvider($this->app))->boot();

        $this->assertEquals(
            config('database.connections.sqlite.database'),
            ServDocker::make()->updateDataDirectoryPath() . 'database.sqlite',
        );
    }
}

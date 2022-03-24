<?php

namespace App\Providers;

use App\Console\Cli;
use App\DockerComposer;
use App\DriverEngine;
use App\NginxComposer;
use App\ProjectSupervisor;
use App\ServDocker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(ServDocker::class, function (): ServDocker {
            return new ServDocker(DockerComposer::make());
        });

        $this->app->singleton(DriverEngine::class, function (): DriverEngine {
            return DriverEngine::make(config('drivers'));
        });

        $this->app->singleton(ProjectSupervisor::class, function ($app): ProjectSupervisor {
            return new ProjectSupervisor($app->make(ServDocker::class), $app->make(DriverEngine::class));
        });

        $this->app->singleton(NginxComposer::class, function (): NginxComposer {
            return NginxComposer::make();
        });

        Config::set(
            'database.connections.sqlite.database',
            ServDocker::make()->updateDataDirectoryPath() . 'database.sqlite'
        );

        $this->app->bind(Cli::class, function (): Cli {
            return tap(new Cli(), static function (Cli $cli): void {
                $cli->setTimeout(config('servd.process_timeout'));
            });
        });
    }
}

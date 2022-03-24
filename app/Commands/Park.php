<?php

namespace App\Commands;

use App\ProjectSupervisor;
use LaravelZero\Framework\Commands\Command;

class Park extends Command
{
    /** @var string */
    protected $signature = 'park';

    /** @var string */
    protected $description = 'Sets the working directory for servd to the current directory';

    public function handle(): void
    {
        $this->info('Setting the servd working directory to the current directory: ' . getcwd());

        ProjectSupervisor::make()->setWorkingDirectory(getcwd());

        $this->info('Working directory successfully updated');
    }
}

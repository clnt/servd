<?php

namespace Tests\Unit\Console;

use App\Console\Cli;
use Tests\TestCase;

class CliTest extends TestCase
{
    /** @test */
    public function it_executes_a_command(): void
    {
        $this->assertSame('up'.PHP_EOL, (new Cli())->exec('echo up'));
        $this->assertSame('up'.PHP_EOL, $this->captureOutput(function (): void {
            app()->make(Cli::class)->execRealTime('echo up');
        }));
    }

    /** @test */
    public function a_cli_instance_has_no_timeout_when_created(): void
    {
        $this->assertNull((new Cli())->getTimeout());
    }

    /** @test */
    public function a_cli_instance_made_by_the_app_has_the_default_timeout(): void
    {
        $this->assertSame(config('servd.process_timeout'), app()->make(Cli::class)->getTimeout());
    }

    /** @test */
    public function the_timeout_for_a_cli_instance_can_be_changed_and_removed(): void
    {
        $cli = new Cli();

        $this->assertNull($cli->getTimeout());

        $cli->setTimeout(60);
        $this->assertSame(60, $cli->getTimeout());

        $cli->doNotTimeout();
        $this->assertNull($cli->getTimeout());
    }

    /**
     * Run the callback and return the captured output.
     */
    protected function captureOutput(callable $callback): false|string
    {
        ob_start();
        $callback();

        return ob_get_clean();
    }
}

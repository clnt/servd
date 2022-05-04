<?php

namespace App\Console;

abstract class CliCommand
{
    protected string $command = '';

    protected bool $interactive = false;

    protected bool $realTime = false;

    protected Cli $cli;

    public function __construct(Cli $cli, string $command = '')
    {
        $this->cli = $cli;
        $this->command = trim($command);
    }

    public static function make(string $command = ''): self
    {
        return app(self::class, ['cli' => app(Cli::class), 'command' => $command]);
    }

    /**
     * Append a bash command, optionally with a further call.
     */
    public function bash(?string $command = null): self
    {
        $this->interactive();
        $this->append('bash');

        if ($command) {
            $this->append("-c \"$command\"");
        }

        return $this;
    }

    /**
     * Execute the command.
     *
     * @return string|int
     */
    public function perform()
    {
        if ($this->isInteractive()) {
            return $this->cli->passthrough($this->prepare());
        }

        if ($this->isRealTime()) {
            return $this->cli->execRealTime($this->prepare());
        }

        return $this->cli->exec($this->prepare());
    }

    /**
     * Append given string to the command.
     */
    public function append(?string $string = null): self
    {
        $this->command = trim($this->command." {$string}");

        return $this;
    }

    /**
     * Set a command as being interactive (i.e. passthrough() in php).
     */
    public function interactive(): self
    {
        $this->interactive = true;

        return $this;
    }

    /**
     * Set a command as not being interactive.
     */
    public function notInteractive(): self
    {
        $this->interactive = false;

        return $this;
    }

    /**
     * Check if the command is expected to be interactive.
     */
    public function isInteractive(): bool
    {
        return $this->interactive;
    }

    /**
     * Set our expectation to see real-time output.
     */
    public function realTime(): self
    {
        $this->realTime = true;

        return $this;
    }

    /**
     * Set our expectation NOT to see real-time output.
     */
    public function notRealTime(): self
    {
        $this->realTime = false;

        return $this;
    }

    /**
     * Check if we're expecting realtime output.
     */
    public function isRealTime(): bool
    {
        return $this->realTime;
    }

    /**
     * Return the Cli instance for this CliCommand.
     */
    public function getCli(): Cli
    {
        return $this->cli;
    }

    /**
     * Set the timeout for the Cli instance.
     */
    public function setTimeout(int $seconds): self
    {
        $this->cli->setTimeout($seconds);

        return $this;
    }

    /**
     * Remove the timeout for the Cli instance. (Just a nicer way to write it).
     */
    public function doNotTimeout(): self
    {
        $this->cli->doNotTimeout();

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}

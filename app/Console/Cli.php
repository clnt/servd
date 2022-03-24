<?php

namespace App\Console;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Cli
{
    /** The process timeout in seconds */
    protected ?int $timeout = null;

    /** Execute a command */
    public function exec(string $command): string
    {
        $process = $this->getProcess($command);
        $process->run();

        return $process->getOutput();
    }

    /** Execute a command in real time */
    public function execRealTime(string $command): int
    {
        $process = $this->getProcess($command);

        try {
            $process->mustRun(function ($type, $buffer): void { //phpcs:ignore
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        return $process->getExitCode();
    }

    /** Execute a command and allow the user to interact with it */
    public function passthrough(string $command): int
    {
        $process = $this->getProcess($command);

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer): void { //phpcs:ignore
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        return $process->getExitCode();
    }

    /** Get a Symfony process object that can execute a command */
    protected function getProcess(string $command): Process
    {
        return Process::fromShellCommandline($command)
            ->setTimeout($this->timeout);
    }

    /** Set the timeout for the wrapping PHP Process */
    public function setTimeout(?int $seconds): Cli
    {
        if ($seconds === null) {
            return $this;
        }

        $this->timeout = $seconds;

        return $this;
    }

    /** Remove the timeout for the wrapping PHP Process */
    public function doNotTimeout(): Cli
    {
        $this->timeout = null;

        return $this;
    }

    /** Return the timeout for the wrapping PHP Process */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }
}

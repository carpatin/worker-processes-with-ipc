<?php

namespace LiveHappyCodeHard\WorkerProcesses;

/**
 * Models a process status
 */
class ProcessStatus
{

    const STATUS_EXITED   = 'exited';
    const STATUS_STOPPED  = 'stopped';
    const STATUS_SIGNALED = 'signaled';

    private $status;

    /**
     * The exit code of the process.
     * This has a valid value only if status == STATUS_EXITED.
     *
     * @var int
     */
    private $exitCode;

    /**
     * The signal received by the process that lead to current status.
     * This has a valid value only if status == STATUS_STOPPED or status == STATUS_SIGNALED.
     *
     * @var int
     */
    private $reasonSignal;

    /**
     * Analyzes the passed status code of a process and sets properties accordingly.
     *
     * @param int $status
     */
    public function __construct($status)
    {
        if (pcntl_wifexited($status))
        {
            $this->status = self::STATUS_EXITED;
            $this->exitCode = pcntl_wexitstatus($status);
        } elseif (pcntl_wifsignaled($status))
        {
            $this->status = self::STATUS_SIGNALED;
            $this->reasonSignal = pcntl_wtermsig($status);
        } elseif (pcntl_wifstopped($status))
        {
            $this->status = self::STATUS_STOPPED;
            $this->reasonSignal = pcntl_wstopsig($status);
        }
    }

    /**
     * Returns process status.
     *
     * @param bool|int $codeOrSignal Extra info about the process depending on the actual status.
     *
     * @return string|null one of the STATUS_* constants or NULL if no status applies.
     */
    public function getStatus(&$codeOrSignal = false)
    {
        if ($this->status === self::STATUS_EXITED)
        {
            $codeOrSignal = $this->exitCode;
        } elseif ($this->status !== null)
        {
            $codeOrSignal = $this->reasonSignal;
        }

        return $this->status;
    }

}

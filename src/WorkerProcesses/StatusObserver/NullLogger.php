<?php

namespace LiveHappyCodeHard\WorkerProcesses\StatusObserver;

use LiveHappyCodeHard\WorkerProcesses\StatusObserverInterface;
use LiveHappyCodeHard\WorkerProcesses\ProcessStatus;
use LiveHappyCodeHard\WorkerProcesses\Task;

/**
 * Default process observer that acts as a stub.
 */
class NullObserver implements StatusObserverInterface
{

    /**
     * Stub.
     *
     * @param int           $pid
     * @param ProcessStatus $status
     * @param Task          $task
     */
    public function notifyProcessStatus($pid, ProcessStatus $status, Task $task)
    {
        // Nothing done here
    }

}

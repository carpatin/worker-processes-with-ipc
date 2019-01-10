<?php

namespace LiveHappyCodeHard\WorkerProcesses;

/**
 * StatusObserver interface.
 * Needs to be implemented by all process status observers used along with an Execution.
 */
interface StatusObserverInterface
{

    /**
     * Notifies the status observer that the process with the provided PID has the
     * status depicted by second argument. Third argument is the task that has been
     * given to the process.
     *
     * @param int           $pid
     * @param ProcessStatus $status
     * @param Task          $task
     */
    function notifyProcessStatus($pid, ProcessStatus $status, Task $task);
}

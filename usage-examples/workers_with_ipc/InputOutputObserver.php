<?php

use LiveHappyCodeHard\WorkerProcesses\Communication\ExecutionObserver;
use LiveHappyCodeHard\WorkerProcesses\Communication\Handle;

/**
 * This is the communication observer for a given concrete task,
 * in this example the task is a UppercaseTask.
 *
 * The communication is a 3-step one:
 * 1 - parent writes to a shared IPC handle -> ExecutionObserver::notifyBeforeExecute()
 * 2 - child reads from handle, processes and then writes back -> Worker::execute()
 * 3 - parent reads data written by child after child finishes execution -> ExecutionObserver::notifyAfterExecute()
 *
 * Parent must wait for all child processes to finish execution by calling Execution::wait()
 * and passing a callback function as parameter; the callback will have access to final
 * state of communication (that means observers fully notified)
 */
class InputOutputObserver implements ExecutionObserver
{

    private $originalString;
    private $finalString;

    function __construct($original)
    {
        $this->originalString = $original;
    }

    function getOriginalString()
    {
        return $this->originalString;
    }

    function getFinalString()
    {
        return $this->finalString;
    }

    public function notifyBeforeExecute(Handle $handle)
    {
        // Open IPC handle to write content for child
        $handle->open();

        // Write the content
        $handle->write($this->originalString);
    }

    public function notifyAfterExecute(Handle $handle)
    {
        // Read processed content received from child
        $this->finalString = $handle->read($handle->size());

        // Delete the IPC handle after read contents from child
        $handle->delete();
    }

}

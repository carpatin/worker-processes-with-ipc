<?php

namespace LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * IPC related execution observer interface
 */
interface ExecutionObserver
{

    /**
     * @param Handle $handle
     */
    function notifyBeforeExecute(Handle $handle);

    /**
     * @param Handle $handle
     */
    function notifyAfterExecute(Handle $handle);
}

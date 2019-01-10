<?php

namespace LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * IPC handles factory interface
 */
interface Factory
{

    /**
     * @return array An array with two handles on the same resource,
     * first for the parent process and second for the child.
     */
    function createHandlesPair();
}

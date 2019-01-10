<?php

namespace LiveHappyCodeHard\WorkerProcesses;

/**
 * Worker interface
 */
interface Worker
{

    /**
     * Contains logic to be executed by the worker.
     *
     * @param Task $task
     */
    function execute(Task $task);
}

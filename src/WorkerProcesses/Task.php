<?php

namespace LiveHappyCodeHard\WorkerProcesses;

/**
 * Task interface. Models a task given to a Worker to execute.
 */
interface Task
{

    /**
     * Returns data to be processed by the Worker.
     *
     * @returns mixed
     */
    function getData();
}

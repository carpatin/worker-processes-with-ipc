<?php

namespace LiveHappyCodeHard\WorkerProcesses\Worker;

/**
 * Worker factory interface
 */
interface Factory
{

    function createWorker();

    function isWorkerCommunicationAware();
}

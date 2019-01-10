<?php

namespace LiveHappyCodeHard\WorkerProcesses\Worker;

use LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * IPC aware worker interface
 */
interface CommunicationAwareInterface
{

    /**
     * Sets IPC handle.
     *
     * @param Communication\Handle $handle
     */
    function setCommunicationHandle(Communication\Handle $handle);

    /**
     * Returns IPC handle.
     *
     * @return Communication\Handle
     */
    function getCommunicationHandle();
}

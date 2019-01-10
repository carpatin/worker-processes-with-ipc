<?php

use LiveHappyCodeHard\WorkerProcesses\Task;
use LiveHappyCodeHard\WorkerProcesses\Worker;
use LiveHappyCodeHard\WorkerProcesses\Communication\Handle;

class TextWorker implements Worker, Worker\CommunicationAwareInterface
{

    /**
     * The IPC handle used by the worker to communicate with the parent.
     *
     * @var Handle
     */
    private $handle;

    /**
     * Implementation of CommunicationAware interface method
     *
     * @return Handle
     */
    public function getCommunicationHandle()
    {
        return $this->handle;
    }

    /**
     * Implementation of CommunicationAware interface method
     *
     * @param \LiveHappyCodeHard\WorkerProcesses\Communication\Handle $handle
     */
    public function setCommunicationHandle(\LiveHappyCodeHard\WorkerProcesses\Communication\Handle $handle)
    {
        $this->handle = $handle;
    }

    /**
     * Implementation of Worker interface method
     *
     * @param Task $task
     */
    public function execute(Task $task)
    {
        // Basically this worker is not tied to uppercase-ing tasks, any
        // text processing related task can be added with corresponding case here
        if ($task instanceof UppercaseTask)
        {
            $this->handleUppercase($task);
        }
    }

    private function handleUppercase($task)
    {
        // Easy debug print message
        print 'WORKER : START task data : '.$task->getData().PHP_EOL;

        // Simulate processing time
        sleep(rand(1,5));

        // Open IPC handle on worker side
        $handle = $this->getCommunicationHandle();
        $handle->open();

        // Read content, process and then write back
        $observerData = $handle->read($handle->size());
        $uppercased = strtoupper($task->getData());
        $handle->write('INPUT/OUTPUT OBSERVER : processed '.$observerData.' : '.$uppercased);

        // Close IPC handle on worker side
        $handle->close();

        // Easy debug print message
        print 'WORKER : FINISHED task data : '.$task->getData().PHP_EOL;
    }

}

<?php

use LiveHappyCodeHard\WorkerProcesses\Task;

class UppercaseTask implements Task
{

    /**
     * @var string
     */
    private $dataToProcess;

    public function __construct($identifier)
    {
        $this->dataToProcess = $identifier;
    }

    /**
     * Implementation of Task interface method.
     *
     * @return string
     */
    public function getData()
    {
        return $this->dataToProcess;
    }
}

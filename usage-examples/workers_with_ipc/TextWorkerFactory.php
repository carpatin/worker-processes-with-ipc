<?php

use LiveHappyCodeHard\WorkerProcesses\Worker\Factory;

class TextWorkerFactory implements Factory
{

    public function createWorker()
    {
        return new TextWorker();
    }

    public function isWorkerCommunicationAware()
    {
        // This is important in order to have IPC
        return true;
    }

}

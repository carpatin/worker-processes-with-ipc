<?php

use LiveHappyCodeHard\WorkerProcesses\Task;
use LiveHappyCodeHard\WorkerProcesses\StatusObserverInterface;
use LiveHappyCodeHard\WorkerProcesses\ProcessStatus;

class StatusObserver implements StatusObserverInterface
{

    public function notifyProcessStatus($pid, ProcessStatus $status, Task $task)
    {
        print "STATUS OBSERVER: worker {$pid} finished with status {$status->getStatus()} doing {$task->getData()}\n";
    }

}

<?php

require '../../vendor/autoload.php';

use LiveHappyCodeHard\WorkerProcesses\Task;
use LiveHappyCodeHard\WorkerProcesses\Worker;
use LiveHappyCodeHard\WorkerProcesses\Execution;
use LiveHappyCodeHard\WorkerProcesses\StatusObserverInterface;
use LiveHappyCodeHard\WorkerProcesses\ProcessStatus;
use LiveHappyCodeHard\WorkerProcesses\Worker\Factory;

class DummyTask implements Task
{

    private $duty;

    public function __construct($duty)
    {
        $this->duty = $duty;
    }

    public function getData()
    {
        return $this->duty;
    }

}

class WorkerFactory implements Factory
{

    public function createWorker()
    {
        return new DummyWorker();
    }

    public function isWorkerCommunicationAware()
    {
        return false;
    }

}

class DummyWorker implements Worker
{

    public function execute(Task $task)
    {
        print "WORKER: {$task->getData()} START".PHP_EOL;
        sleep(rand(1, 5));
        print "WORKER: {$task->getData()} FINISHED".PHP_EOL;
    }

}

class DebugObserver implements StatusObserverInterface
{

    public function notifyProcessStatus($pid, ProcessStatus $status, Task $task)
    {
        print "DEBUG OBSERVER: Worker {$pid} finished with status {$status->getStatus()} doing {$task->getData()}\n";
    }

}

$t1 = new DummyTask('one');

$t2 = new DummyTask('two');

$t3 = new DummyTask('three');

$t4 = new DummyTask('four');

$t5 = new DummyTask('five');

$t6 = new DummyTask('six');

$debugObserver = new DebugObserver();
$workerFactory = new TextWorkerFactory();

$e = new Execution($workerFactory, 3, $debugObserver);

$e->execute($t1);
$e->execute($t2);
$e->execute($t3);
$e->execute($t4);
$e->execute($t5);
$e->execute($t6);

$e->wait(
    function () {

        print 'PARENT: WAITED FOR ALL CHILDREN'.PHP_EOL;
    }
);

print 'PARENT: FINISHED'.PHP_EOL;

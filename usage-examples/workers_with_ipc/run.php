<?php

require '../../vendor/autoload.php';

require_once 'InputOutputObserver.php';
require_once 'TextWorkerFactory.php';
require_once 'StatusObserver.php';
require_once 'TextWorker.php';
require_once 'UppercaseTask.php';

use LiveHappyCodeHard\WorkerProcesses\Execution;
use LiveHappyCodeHard\WorkerProcesses\Communication\SharedMemory\Factory as SharedMemoryHandleFactory;

print 'PARENT START'.PHP_EOL;

$t1 = new UppercaseTask('big');
$t2 = new UppercaseTask('brown');
$t3 = new UppercaseTask('bear');
$t4 = new UppercaseTask('fooled');
$t5 = new UppercaseTask('by the');
$t6 = new UppercaseTask('fox');

$obs1 = new InputOutputObserver('observer 1');
$obs2 = new InputOutputObserver('observer 2');
$obs3 = new InputOutputObserver('observer 3');
$obs4 = new InputOutputObserver('observer 4');
$obs5 = new InputOutputObserver('observer 5');
$obs6 = new InputOutputObserver('observer 6');

$workerFactory = new TextWorkerFactory();
$ipcHandleFactory = new SharedMemoryHandleFactory();
$statusObserver = new StatusObserver();

$e = new Execution($workerFactory, 2, $statusObserver, $ipcHandleFactory);

$e->execute($t1, $obs1);
$e->execute($t2, $obs2);
$e->execute($t3, $obs3);
$e->execute($t4, $obs4);
$e->execute($t5, $obs5);
$e->execute($t6, $obs6);

$e->wait(
    function () use ($obs1, $obs2, $obs3, $obs4, $obs5, $obs6) {
        print 'OBSERVERS OUTPUT: '.PHP_EOL;

        print $obs1->getFinalString().PHP_EOL;
        print $obs2->getFinalString().PHP_EOL;
        print $obs3->getFinalString().PHP_EOL;
        print $obs4->getFinalString().PHP_EOL;
        print $obs5->getFinalString().PHP_EOL;
        print $obs6->getFinalString().PHP_EOL;
    }
);

print 'PARENT DONE'.PHP_EOL;

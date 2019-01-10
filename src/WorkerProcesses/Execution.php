<?php

namespace LiveHappyCodeHard\WorkerProcesses;

use LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * Models a pooled process execution.
 * Acts as a manager of child worker processes.
 */
class Execution
{

    /**
     * Maximum number of worker processes running at any given time
     *
     * @var int
     */
    private $maxWorkers;

    /**
     * IPC communication handle factory instance
     *
     * @var Communication\Factory
     */
    private $ipcFactory;

    /**
     * Workers factory instance
     *
     * @var Worker\Factory
     */
    private $workerFactory;

    /**
     * Stores forked worker processes PIDs
     *
     * @var array
     */
    private $workerPids;

    /**
     * Keeps track of tasks passed to spawn workers.
     *
     * @var array
     */
    private $workerTasks;

    /**
     * IPC execution observers
     *
     * @var array
     */
    private $ipcExecutionObservers;

    /**
     * Keeps track of IPC handles on the parent side.
     *
     * @var array
     */
    private $ipcHandles;

    /**
     * Flag used for identifying the parent process.
     *
     * @var boolean
     */
    private $isParent;

    /**
     * Process status observer
     *
     * @var StatusObserverInterface
     */
    private $statusObserver;

    /**
     * Initializes properties.
     *
     * @param Worker\Factory          $workerFactory
     * @param int                     $maxWorkers
     * @param StatusObserverInterface $statusObserver
     * @param Communication\Factory   $ipcFactory
     */
    public function __construct(
        Worker\Factory $workerFactory,
        $maxWorkers = 3,
        StatusObserverInterface $statusObserver = null,
        Communication\Factory $ipcFactory = null
    ) {
        $this->workerFactory = $workerFactory;
        $this->maxWorkers = $maxWorkers;
        // Initialize workers trace metadata structures
        $this->workerPids = [];
        $this->workerTasks = [];
        $this->ipcHandles = [];
        $this->ipcExecutionObservers = [];

        // At this point the process that runs its the parent
        $this->isParent = true;

        $this->setIpcFactory($ipcFactory);
        $this->setStatusObserver($statusObserver);
    }

    /**
     * Setter for IPC handles factory.
     *
     * @param Communication\Factory $ipcFactory
     */
    public function setIpcFactory(Communication\Factory $ipcFactory = null)
    {
        $this->ipcFactory = $ipcFactory;
    }

    /**
     * Sets spawned processes status observer.
     *
     * @param setStatusObserver $statusObserver
     */
    public function setStatusObserver(StatusObserverInterface $statusObserver = null)
    {
        if ($statusObserver === null)
        {
            $this->statusObserver = new StatusObserver\NullObserver();
        } else
        {
            $this->statusObserver = $statusObserver;
        }
    }

    /**
     * Waits for forked children.
     */
    public function __destruct()
    {
        if (!$this->isParent)
        {
            // If the destructor runs in child process nothing needs to be done
            return;
        }

        // Wait for all unfinished child processes
        $this->wait();
    }

    /**
     * Waits for all remaining child processes.
     *
     * @param callable $callback Callback function executed after all finish execution.
     */
    public function wait(callable $callback = null)
    {

        foreach ($this->workerPids as $pid)
        {
            // Wait for the current process to finish
            $status = 0;
            pcntl_waitpid($pid, $status);

            // Process the finished worker state
            $this->processFinishedWorker($pid, $status);
        }

        if ($callback !== null)
        {
            call_user_func($callback);
        }
    }

    /**
     * Executes a given task.
     * Pools a worker slot, spawns a new worker and passes it the task to accomplish.
     *
     * @param Task                            $task
     * @param Communication\ExecutionObserver $observer
     */
    public function execute(Task $task, Communication\ExecutionObserver $observer = null)
    {
        if (count($this->workerPids) == $this->maxWorkers)
        {
            $this->waitWorker();
        }
        $this->spawnWorker($task, $observer);
    }

    /**
     * Waits any of the spawned worker processes to finish.
     * Then frees the slot previously used by it.
     */
    private function waitWorker()
    {

        // Wait for a process to finish
        $status = 0;
        $pid = pcntl_wait($status);

        // Process the finished worker state
        $this->processFinishedWorker($pid, $status);
    }

    /**
     * Processes a finished worker state: notifies observers about the event.
     *
     * @param int $pid
     * @param int $status
     */
    private function processFinishedWorker($pid, $status)
    {

        // Notify the waited process status
        $processStatus = new ProcessStatus($status);
        $task = $this->workerTasks[$pid];
        $this->statusObserver->notifyProcessStatus($pid, $processStatus, $task);

        // If IPC is enabled, notify the fact that the child finished, passing the handle
        if ($this->isIpcEnabled())
        {
            $handle = $this->ipcHandles[$pid];
            /* @var $observer Communication\ExecutionObserver */
            $observer = $this->ipcExecutionObservers[$pid];
            $observer->notifyAfterExecute($handle);
        }

        // Clean process metadata
        $this->cleanWorkerTraceMetadata($pid);
    }

    /**
     * Returns whether IPC is enabled for the execution.
     *
     * @return boolean
     */
    public function isIpcEnabled()
    {
        return $this->ipcFactory !== null && $this->workerFactory->isWorkerCommunicationAware();
    }

    /**
     * Spawns a new worker and passses the task to it for execution.
     *
     * @param Task                            $task
     * @param Communication\ExecutionObserver $observer
     */
    private function spawnWorker(Task $task, Communication\ExecutionObserver $observer = null)
    {
        $parentHandle = $childHandle = null;
        if ($this->isIpcEnabled())
        {
            list($parentHandle, $childHandle) = $this->ipcFactory->createHandlesPair();

            if ($observer !== null)
            {
                $observer->notifyBeforeExecute($parentHandle);
            }
        }

        if (($pid = pcntl_fork()) == 0)
        {
            // Code executed in child process
            $this->isParent = false;
            $worker = $this->workerFactory->createWorker();

            if ($worker instanceof Worker\CommunicationAwareInterface)
            {
                $worker->setCommunicationHandle($childHandle);
            }

            $worker->execute($task);
            exit(0);
        } else
        {
            // Code executed in parent process
            $this->workerPids[] = $pid;
            $this->workerTasks[$pid] = $task;

            // Check whether the IPC is enabled, and store related for later use
            if ($parentHandle !== null)
            {
                $this->ipcHandles[$pid] = $parentHandle;
                $this->ipcExecutionObservers[$pid] = $observer;
            }
        }
    }

    /**
     * Unsets all metadata kept regarding the given PID.
     *
     * @param int $pid
     */
    private function cleanWorkerTraceMetadata($pid)
    {
        unset($this->workerPids[array_search($pid, $this->workerPids)]);
        unset($this->workerTasks[$pid]);
        if (isset($this->ipcHandles[$pid]))
        {

            // Delete the handle before erasing metadata about it
            /* @var $handle Communication\Handle */
            $handle = $this->ipcHandles[$pid];
            $handle->delete();

            unset($this->ipcHandles[$pid]);
        }
        if (isset($this->ipcExecutionObservers[$pid]))
        {
            unset($this->ipcExecutionObservers[$pid]);
        }
    }

}

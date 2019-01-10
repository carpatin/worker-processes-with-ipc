<?php

namespace LiveHappyCodeHard\WorkerProcesses\Communication\SharedMemory;

use LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * Factory for IPC handles based of shared memory
 */
class Factory implements Communication\Factory
{

    /**
     * Shared memory blocks size in bytes.
     *
     * @var int
     */
    private $blockSize;

    /**
     * Sets up factory instance.
     *
     * @param int $blockSize Shared memory block size
     */
    public function __construct($blockSize = 1024)
    {
        $this->setBlockSize($blockSize);
    }

    function getBlockSize()
    {
        return $this->blockSize;
    }

    function setBlockSize($blockSize)
    {
        $this->blockSize = $blockSize;
    }

    /**
     * Creates and returns pair of communication means handles.
     *
     * @return array
     * @throws \Exception
     */
    public function createHandlesPair()
    {

        // Attempt a creation of a new shared memory block
        $key = $this->createSharedMemoryBlock();

        // Instantiate parent and child handles for the created block
        $parentHandle = new Handle($key);
        $childHandle = new Handle($key);

        return [$parentHandle, $childHandle];
    }

    /**
     * Creates new shared memory block and reurns the key used to create it.
     *
     * @return int The key / system's id for the memory block.
     * @throws \Exception
     */
    private function createSharedMemoryBlock()
    {
        $attempts = 0;
        $shm = false;

        while ($attempts < 100)
        {
            $key = $this->generateKey();
            $shm = shmop_open($key, 'n', 0644, $this->blockSize);
            if ($shm !== false)
            {
                break;
            }
            $attempts++;
        }

        if ($shm === false)
        {
            throw new \Exception('Cannot create new shared memory block');
        }

        shmop_close($shm);

        return $key;
    }

    /**
     * Generates a key usable to create a shared memory block.
     *
     * @return int
     */
    private function generateKey()
    {
        // Simple implementation, maybe it could be improved
        return rand(0, pow(2, 32));
    }

}

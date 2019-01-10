<?php

namespace LiveHappyCodeHard\WorkerProcesses\Communication\SharedMemory;

use LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * IPC handle implemented based on shared memory (shmop extension)
 */
class Handle implements Communication\Handle
{

    /**
     * @var string
     */
    private $shmKey;

    /**
     * @var resource
     */
    private $shmId;

    const STATUS_INITIAL = 'initial';
    const STATUS_OPENED  = 'opened';
    const STATUS_CLOSED  = 'closed';
    const STATUS_DELETED = 'deleted';

    private $status;

    public function __construct($shmKey)
    {
        $this->shmKey = $shmKey;
        $this->status = self::STATUS_INITIAL;
    }

    public function close()
    {
        if ($this->status == self::STATUS_OPENED)
        {
            shmop_close($this->shmId);
            $this->status = self::STATUS_CLOSED;
        }
    }

    public function delete()
    {
        if ($this->status == self::STATUS_DELETED)
        {
            return true;
        }

        // Reopen segment if not already opened
        if ($this->status != self::STATUS_OPENED)
        {
            $this->open();
        }

        $result = shmop_delete($this->shmId);
        $this->shmId = null;
        $this->status = self::STATUS_DELETED;

        return $result;
    }

    public function open()
    {
        if ($this->status == self::STATUS_OPENED)
        {
            return;
        }

        if ($this->status == self::STATUS_INITIAL || $this->status == self::STATUS_CLOSED)
        {
            $this->shmId = shmop_open($this->shmKey, 'w', 0, 0);

            if ($this->shmId === false)
            {
                throw new \Exception('Cannot open shared memory block');
            }
            $this->status = self::STATUS_OPENED;
        } else
        {
            throw new \Exception('Cannot open a deleted memory block');
        }
    }

    public function size()
    {
        if ($this->status == self::STATUS_OPENED)
        {
            return shmop_size($this->shmId);
        }

        return 0;
    }

    public function read($length = 0, $from = 0)
    {
        $data = null;
        if ($this->status == self::STATUS_OPENED)
        {
            $data = shmop_read($this->shmId, $from, $length);
        }

        // Remove trailing empty bytes
        $data = trim($data, "\0");

        return $data;
    }

    public function write($data, $from = 0)
    {
        $count = false;
        if ($this->status == self::STATUS_OPENED)
        {
            $count = shmop_write($this->shmId, $data, $from);
        }

        return $count;
    }

}

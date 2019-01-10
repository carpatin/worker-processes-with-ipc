<?php

namespace LiveHappyCodeHard\WorkerProcesses\Communication;

/**
 * IPC handle interface
 */
interface Handle
{
    /**
     * @return void
     */
    function open();

    /**
     * @return void
     */
    function close();

    /**
     * @return boolean
     */
    function delete();

    /**
     * @return int
     */
    function size();

    /**
     * @param int $length
     * @param int $from
     *
     * @return string
     */
    function read($length = 0, $from = 0);

    /**
     * @param string $data
     * @param int    $from
     */
    function write($data, $from = 0);
}

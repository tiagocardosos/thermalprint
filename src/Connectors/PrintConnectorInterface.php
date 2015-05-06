<?php

namespace ThermalPrint\Connectors;

interface PrintConnector
{
    /**
     * Print connectors should cause a NOTICE if they are deconstructed
     * when they have not been finalized.
     */
    public function __destruct();

    public function initialize();
    
    public function close();
    
    /**
     * Finish using this print connector (close file, socket, send
     * accumulated output, etc).
     */
    public function finalize();

    /**
     * @param string $data
     */
    public function write($data);
}

<?php

namespace ThermalPrint\Connectors;

use ThermalPrint\Connectors\PrintConnector;

class FileConnector implements PrintConnector
{
    /**
     * @var resource The file pointer to send data to.
     */
    protected $fpointer;

    /**
     * Construct new connector, given a filename
     * 
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->fpointer = fopen($filename, "wb+");
        if ($this->fpointer === false) {
            throw new Exception("Cannot initialise FilePrintConnector.");
        }
    }
    
    public function __destruct()
    {
        if ($this->fpointer !== false) {
            trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
        }
    }

    /**
     * Write data to the file
     * 
     * @param string $data
     */
    public function write($data)
    {
        fwrite($this->fpointer, $data);
    }

    /**
     * Close file pointer
     */
    public function finalize()
    {
        fclose($this->fpointer);
        $this->fpointer = false;
    }
}

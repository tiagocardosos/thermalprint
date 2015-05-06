<?php

namespace ThermalPrint\Connectors;

abstract class Device implements PrintConnector
{
    abstract public function close();
    abstract public function finalize();
    abstract public function write($data);

    public function initialize()
    {
        $this->write(EscPos::CTL_ESC . "@");
    }

    public function __destruct()
    {
        $this->close();
    }
}

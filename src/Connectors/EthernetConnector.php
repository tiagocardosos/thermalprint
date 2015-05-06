<?php
namespace ThermalPrint\Connectors;

use ThermalPrint\Connectors\FileConnector;

class NetworkConnector extends FileConnector
{
    public function __construct($ipaddress, $port = "9100")
    {
        $this->fpointer = @fsockopen($ipaddress, $port, $errno, $errstr);
        if ($this->fpointer === false) {
            throw new Exception("Cannot initialise NetworkPrintConnector: " . $ipaddress . $port . $errno . $errstr);
        }
    }
}

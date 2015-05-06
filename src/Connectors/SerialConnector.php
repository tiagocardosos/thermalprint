<?php
namespace ThermalPrint\Connectors;

use ThermalPrint\Connectors\Device;
use \PhpSerial;

class SerialConnector extends Device
{
    protected $resource;

    /**
     * @param string $devfile
     * @param int $baudRate
     * @param int $byteSize
     * @param string $parity
     *
     * @return Serial
     *
     * @codeCoverageIgnore
     */
    public static function create($devfile = "/dev/ttyS0", $baudRate = 9600, $byteSize = 8, $parity = 'none')
    {
        return new self(
            new \PhpSerial(),
            $devfile,
            $baudRate,
            $byteSize,
            $parity
        );
    }

    public function __construct(\PhpSerial $phpSerial, $devfile, $baudRate, $byteSize, $parity)
    {
        $this->resource = $phpSerial;
        $this->resource->deviceSet($devfile);
        $this->resource->confBaudRate($baudRate);
        $this->resource->confCharacterLength($byteSize);
        $this->resource->confParity($parity);
        $this->resource->deviceOpen();
    }

    public function close()
    {
        $this->resource->deviceClose();
    }
    
    public function finalize()
    {
        $this->resource->deviceClose();
    }

    public function write($data)
    {
        $this->resource->sendMessage($data);
    }
}

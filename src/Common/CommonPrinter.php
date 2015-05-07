<?php

namespace ThermalPrint\Common;

use ThermalPrint\Common\PrintInterface;
use ThermalPrint\EscPos;
use \stdClass;

abstract class CommonPrinter implements PrinterInterface
{
    public $device;
    
    /**
     * será substituido nas 
     */
    public function __construct()
    {
        $this->device = \stdClass();
    }
    
    public function initialize()
    {
        
    }
    
    public function cut()
    {
        
    }
    public function send()
    {
        
    }
    public function text()
    {
        
    }
    public function barcode25()
    {
        
    }
    public function barcode128()
    {
        
    }
    public function barcodeQR()
    {
        
    }
    public function barcodePdf()
    {
        
    }
    public function barcode39()
    {
        
    }
    
    public function feed($lines = 1)
    {
        if ($lines <= 1) {
            $this->device->write(EscPos::CTL_LF);
        } else {
            $this->device->write(EscPos::CTL_ESC . "d" . chr($lines));
        }
        return $this;
    }
    
    public function reverseFeed()
    {
        
    }
    public function pulse()
    {
        
    }
    public function putImage()
    {
        
    }
    public function setEmphasis()
    {
        
    }
    public function setItalic()
    {
        
    }
    public function setDoubleStrike()
    {
        
    }
    public function setUnderline()
    {
        
    }
    public function setFontHW()
    {
        
    }
    public function setJustification()
    {
        
    }
    public function setReverseColors()
    {
        
    }
    public function setFontMode()
    {
        
    }
    public function setCharset()
    {
        
    }
    public function close()
    {
        
    }
}

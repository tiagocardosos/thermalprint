<?php

namespace ThermalPrint\Common;

interface PrintInterface
{
    public function initialize();
    public function cut();
    public function send();
    public function text();
    public function barcode25();
    public function barcode128();
    public function barcodeQR();
    public function barcodePdf();
    public function barcode39();
    public function feed();
    public function reverseFeed();
    public function pulse();
    public function putImage();
    public function setEmphasis();
    public function setItalic();
    public function setDoubleStrike();
    public function setUnderline();
    public function setFontHW();
    public function setJustification();
    public function setReverseColors();
    public function setFontMode();
    public function setCharset();
    public function close();
}

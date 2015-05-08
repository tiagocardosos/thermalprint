<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ThermalPrint\ZebraEPL;

class Printer
{
    const CR = "\r";
    const LF = "\n";
    const CRLF = CR.LF;
    
    public static $dpi = 203;
    public static $dpmm = 8;
    public static $wMaxmm = 104.1;
    public static $mLeftmm = 1.3;
    public static $mRightmm = 1.3;
    public static $lMaxmm = 80;
    //GAP 16-240 (dots) for 203 dpi printers
    //GAP 18-240 (dots) for 300dpi printers
    public static $gMaxmm = 3.15;
    public $charset = '';
    
    public $device = '';
    
    public function __construct()
    {
        $this->device = \stdClass();
        $this->device->buffer = '';
    }
    
    /**
     * setDPI
     * Ajusta o numero de dots por polegada 
     * pode ser 203 ou 300 dpi dependendo do modelo de impressora
     * normalmente 203dpi
     * @param int $dpi
     * @return string
     */
    public function setDPI($dpi = 203)
    {
        if ($dpi != 203 && $dpi != 300) {
            return '';
        }
        self::$dpi = $dpi;
        self::$dpmm = (int) round($dpi/25.4, 0);
        return $dpi;
    }
    
    /**
     * setCharSet
     * Configura o charset a ser usado
     * @param int $charset
     * @param string $country
     */
    public function setCharSet($charset = 1252, $country = '')
    {
        $charsetList = array(
            '437' => array ('value' => 0, 'desc' => 'DOS 437 English - US USA'),
            '850' => array('value' => 1, 'desc' => 'DOS 850 Latin 1 British'),
            '851' => array('value' => 12, 'desc' => 'DOS 851 Greek 1'),
            '852' => array('value' => 2, 'desc' => 'DOS 852 Latin 2 (Cyrillic II/Slavic) German'),
            '855' => array('value' => 9, 'desc' => 'DOS 855 Cyrillic'),
            '857' => array('value' => 6, 'desc' => 'DOS 857 Turkish Spanish'),
            '860' => array('value' => 3, 'desc' => 'DOS 860 Portuguese French'),
            '861' => array('value' => 7, 'desc' => 'DOS 861 Icelandic Swedish'),
            '862' => array('value' => 8, 'desc' => 'DOS 862 Hebrew Swiss'),
            '863' => array('value' => 4, 'desc' => 'DOS 863 French Canadian Danish'),
            '865' => array('value' => 5, 'desc' => 'DOS 865 Nordic Italian'),
            '866' => array('value' => 10, 'desc' => 'DOS 866 Cyrillic CIS 1'),
            '869' => array('value' => 13, 'desc' => 'DOS 869 Greek 2'),
            '737' => array('value' => 11, 'desc' => 'DOS 737 Greek'),
            '1250' => array('value' => 'B', 'desc' => 'Windows 1250 Latin 2'),
            '1251' => array('value' => 'C', 'desc' => 'Windows 1251 Cyrillic'),
            '1252' => array('value' => 'A', 'desc' => 'Windows 1252 Latin 1'),
            '1253' => array('value' => 'D', 'desc' => 'Windows 1253 Greek'),
            '1254' => array('value' => 'E', 'desc' => 'Windows 1254 Turkish'),
            '1255' => array('value' => 'F', 'desc' => 'Windows 1255 Hebrew'));
        
        $countryList = array(
            '' => '',
            'USA' => '001',
            'Canada' => '002',
            'Latin Am' => '003',
            'S Africa' => '027',
            'Belgium' => '032',
            'Netherlds' => '031',
            'France' => '033',
            'Spain' => '034',
            'Italy' => '039',
            'Swizerld' => '041',
            'UK' => '044',
            'Denmark' => '045',
            'Sweden' => '046',
            'Norway' => '047',
            'Germany' => '049',
            'Portugal' => '351',
            'Finland' => '358');
        $this->charset = $charset;
        if ($charset > 1100) {
            $this->charset = 'CP'.$charset;
        }
        $command = 'I8,'. $charsetList[$charset]['value'];
        if ($country != '') {
            $command .= ','.$countryList[$country].self::CRLF;
        }
        $this->device->buffer .= $command;
    }
    
    /**
     * setWidth
     * Seta a largura do papel ou etiqueta
     * @param number $width
     * @return string
     */
    public function setWidth($width = 0)
    {
        if ($width == 0) {
            $width = self::$wMaxmm - (self::$mLeftmm + self::$mRightmm);
        }
        $command = 'q'.self::zMm2dots($width).self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }

    /**
     * setLength
     * @param number $length
     * @param number $gap
     * @param number $offset
     * @return string
     */
    public function setLength($length = 0, $gap = 0, $offset = 0)
    {
        if ($lenght < 0 || $length >  65535) {
            return '';
        }
        if ((self::$dpi == 203 && ($gap < 2 || $gap > 30)) ||
            (self::$dpi == 300 && ($gap < 1.54 || $gap > 20.3))) {
                return '';
        }
        if ($length == 0) {
            $lenght = self::$lMaxmm;
        }
        $command = 'Q'.self::zMm2dots($lenght).','.self::zMm2dots($gap);
        if ($offset != 0) {
            $command .= ','.self::zMm2dots($offset);
        }
        $command .= self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * enableTopFormBack
     * Ativar Top Of Form backup
     * Este comando permite que o recurso "Top Of Form Backup" e apresenta o última
     * etiqueta de uma operação de impressão em lote.
     * Mediante pedido de iniciar a impressão da forma seguinte (ou em lotes),
     * a última etiqueta faz o backup do topo do formulário antes de imprimir a próxima etiqueta.
     * @return string
     */
    public function enableTopFormBack()
    {
        $command = 'JF'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Windows Mode
     * Este comando é usado para desativar/ativar o modo de comando do Windows.
     * Quando ativado, a impressora irá aceitar seqüências de escape modo Windows 
     * para imprimir dados. quando desativado, seqüências de escape serão ignoradas.
     * As sequências de escape do Windows são utilizados apenas pelo driver de 
     * impressora do Windows opcional.
     * Ao trabalhar com uma estrutura principal ou outro host não-Windows,
     * este modo pode ser desativado para impedir um funcionamento irregular.
     * @param bool $flagWm
     * @return string
     */
    public function setWindowsMode($flagWm = false)
    {
        $command = 'WN';
        if ($flagWm) {
            $command = 'WY';
        }
        $command .= self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Use esse comando para definir a orientação da impressão
     * @param type $forward
     * @return string
     */
    public function setPrintDirection($forward = true)
    {
        $command = 'ZB';
        if ($forward) {
            $command = 'ZT';
        }
        $command .= self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * finalize
     * @param int $ncopies
     * @return string
     */
    public function finalize($ncopies = 1)
    {
        $command = 'P'.$ncopies.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * storeGraphic
     * @param string $name
     * @param string $graphic
     * @return string
     */
    public function storeGraphic($name = '', $graphic = '')
    {
        $name = substr($name, 0, 8);
        if (is_file($graphic)) {
            $graphic = file_get_contents($graphic);
        }
        $len = strlen($graphic);
        $command = 'GM"'.$name.'","'.$graphic.'"'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    public function drawDirectGraphic($xPos = 0, $yPos = 0, $graphic = '')
    {
        if (is_file($graphic)) {
            $graphic = file_get_contents($graphic);
        }
        $type = exif_imagetype($graphic);
        //1	IMAGETYPE_GIF
        //2	IMAGETYPE_JPEG
        //3	IMAGETYPE_PNG
        //4	IMAGETYPE_SWF
        //5	IMAGETYPE_PSD
        //6	IMAGETYPE_BMP
        //7	IMAGETYPE_TIFF_II (intel byte order)
        //8	IMAGETYPE_TIFF_MM (motorola byte order)
        //9	IMAGETYPE_JPC
        //10	IMAGETYPE_JP2
        //11	IMAGETYPE_JPX
        //12	IMAGETYPE_JB2
        //13	IMAGETYPE_SWC
        //14	IMAGETYPE_IFF
        //15	IMAGETYPE_WBMP
        //16	IMAGETYPE_XBM
        switch ($type) {
            case 1:
                $image = imagecreatefromgif($graphic);
                break;
            case 2:
                $image = imagecreatefromjpg($graphic);
                break;
            case 3:
                $image = imagecreatefrompng($graphic);
                break;
            case 4:
                break;
            case 5:
                break;
            case 6:
                $image = imagecreatefrombmp($graphic);
                break;
            default:
                $image = null;
        }
        if (! isset($image)) {
            return '';
        }
        $width = imagesx($image);
        $heigth = imagesy($image);
        //Multiply the width in bytes (p3) by the number of print lines (p4)
        //for the total amount of graphic data
        $len = strlen($graphic);
        $calc = $width/8 * $heigth;
        if ($len != $calc) {
            echo "ERRRO";
            exit();
        }
        $command = 'GW'
            . self::zMm2dots($xPos) . ','
            . self::zMm2dots($yPos) . ','
            . $width / 8 . ','
            . $heigth . ','
            . $graphic
            . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * cancelSoftOptions
     * This command allows the user to cancel most printer customization parameters
     * set by o series commands.
     * Parameters set by the following commands are canceled and returned to default operation:
     *   • oH
     *   • oM
     *   • oE
     * Parameters There are no parameters for this format.
     * The o command is a global printer command.
     *       • It can not be issued inside of a form.
     *       • It must be issued prior to issuing a text or bar code command (and printing).
     */
    public function cancelSoftOptions()
    {
        $command = 'O'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Box Draw
     * Description Use this command to draw a box shape.
     * Parameters This table identifies the parameters for this format:
     *   Parameters Details
     *       p 1 = Horizontal start position Horizontal start position (X) in dots.
     *       p 2 = Vertical start position Vertical start position (Y) in dots.
     *       p 3 = Line thickness Line thickness in dots.
     *       p 4 = Horizontal end position Horizontal end position (X) in dots.
     *       p 5 = Vertical end position Vertical end position (Y) in dots.
     *   Example • This example will produce the results shown below.
     *   N↵
     *   X50,200,5,400,20↵
     *   X200,50,10,20,400↵
     *   P1↵
     */
    public function drawBox($xIni = 0, $yIni = 0, $wLine = 1, $xFin = 10, $yFin = 10)
    {
        if ($wLine < 1/self::$dpmm) {
            $wLine = 1;
        }
        $command = 'X'
            . self::zMm2dots($xIni).','
            . self::zMm2dots($yIni).','
            . self::zMm2dots($wLine).','
            . self::zMm2dots($xFin).','
            . self::zMm2dots($yFin).','
            . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Reset Printer
     * Description This command is used to reset the printer.
     * where ^ is 94 decimal
     *   This command emulates Power Off and then Power On; thus reinitializing the printer.
     *   • The reset command is unavailable during the operation of storing PCX graphics, soft fonts
     *     or while the printer is in dump mode.
     *   • The reset command cannot be used in a stored form.
     *   • The reset command can be sent to the printer during all other printing operations.
     *   • The printer will ignore all commands sent while the reset command is executing, up to 2
     *     seconds.
     * Parameters There are no parameters for this format.
     * Example • This example causes the printer to reset.
     * ^@↵
     */
    public function resetPrinter()
    {
        $command = chr(94).'@'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
        
    }
    
    /**
     * Set Printer to Factory Defaults
     * Use this command to return the printer to its default configuration.
     * The ^default command resets the density, speed, sensors, image buffer parameters,
     * character code page (including re-mapped characters), options, feed button behaviors, gap
     * mode media sensing, serial interface configuration, error reporting and line mode
     * configuration defaults.
     * This command is intended for troubleshooting and by service organizations. Do not use this
     * command in regular programming! Do not use this command to initialize the printer! This
     * overwrites all stored parameters. The programmer should always minimize writing to the non-
     * volatile 'flash' printer memory.
     */
    public function setPrinterToDefault()
    {
        $command = chr(94).'default'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * ASCII Text
     *   Description Renders an ASCII text string to the image print buffer. See Text (Fonts)
     * on page 28 for discussion on text handling in Page Mode programming.
     * Asian language EPL2 Page Mode printers have special firmware and printer (PCBA) memory
     * order options to support the large Asian character (ideogram) sets.
     * The Latin (English, etc.) font sets (1-5, a-z, and A-Z) are single-byte (8 bits per byte) ASCII
     * character maps. The Asian characters are double-byte mapped characters. The printed Asian
     * character is dependent on the double-byte ASCII values.
     * Syntax Ap 1 ,p 2 ,p 3 ,p 4 ,p 5 ,p 6 ,p 7 ,”DATA”
     * Parameters This table identifies the parameters for this format:
     * Parameters Details
     * p 1 = Horizontal start position Horizontal start position (X) in dots.
     * p 2 = Vertical start position Vertical start position (Y) in dots.
     * p 3 = Rotation Characters are organized vertically from left to right and then rotated to print.
     *          Accepted Values:
     *              0 = normal (no rotation)
     *              1 = 90 degrees
     *              2 = 180 degrees
     *              3 = 270 degrees
     * Rotation for Asian Printers Only
     *      Characters are organized horizontally from top to bottom and
     *      then rotated to print. Asian printers support both horizontal
     *      and vertical character rotation.
     *      Accepted Values: (Asian Printers Only)
     *              4 = normal (no rotation)
     *              5 = 90 degrees
     *              6 = 180 degrees
     *              7 = 270 degrees 
     * p 4 = Font selection
     *      Description
     *          Value
     *              203 dpi
     *              300 dpi
     *      1 20.3 cpi, 6 pts, (8 x 12 dots)
     *      2 16.9 cpi, 7 pts, (10 x 16 dots) 18.75 cpi, 6 pts, (16 x 28 dots)
     *      3 14.5 cpi, 10 pts, (12 x 20 dots) 15 cpi, 8 pts, (20 x 36 dots)
     *      4 12.7 cpi, 12 pts, (14 x 24 dots) 12.5 cpi, 10 pts, (24 x 44 dots)
     *      5 5.6 cpi, 24 pts, (32 x 48 dots) 6.25 cpi, 21 pts, (48 x 80 dots)
     * A - Z  a-z  25 cpi, 4 pts, (12 x 20 dots)
     * Reserved for Soft Font storage.
     * Reserved for printer driver support for storage of user-selected Soft
     * Fonts.
     *      6 Numeric Only (14 x 19 dots) Numeric Only (14 x 19 dots)
     *      7 Numeric Only (14 x 19 dots) Numeric Only (14 x 19 dots)
     * Asian Printers
     *      8 Simplified Chinese, Japanese, Korean
     *         203 dpi fonts : 24 x 24 dots
     *         300 dpi Double-byte fonts: 36 x 36 dots
     *         300 dpi Single-byte fonts: 24 x 26 dots
     *      9 Traditional Chinese, Japanese
     *         300 dpi Double-byte fonts: 36 x 36 dots
     *         300 dpi Single-byte fonts: 24 x 26 dots
     * Korean - Reserved
     * 
     * p 5 = Horizontal multiplier Horizontal multiplier expands the text horizontally.
     *       Accepted Values: 1–6, 8
     * p 6 = Vertical multiplier Vertical multiplier expands the text vertically.
     *       Accepted Values: 1–9
     * p 7 = Reverse image Accepted Values:
     *       N = normal
     *       R = reverse image
     * DATA = Fixed data field Fixed data field
     * The backslash (\) character designates the following character
     * is a literal and will encode into the data field. 14245L-003 Rev. A
     * To Print Enter into data field
     * " \"
     * "Company" \"Company\"
     * \ \\
     * \code\ \\code\\
     * In this example, font 5 only supports upper case characters. Refer to Appendix B,
     * Character References, for a complete listing of available fonts and character sets supported.
     * N↵
     * A50,0,0,1,1,1,N,"Example 1"↵
     * A50,50,0,2,1,1,N,"Example 2"↵
     * A50,100,0,3,1,1,N,"Example 3"↵
     * A50,150,0,4,1,1,N,"Example 4"↵
     * A50,200,0,5,1,1,N,"EXAMPLE 5"↵
     * A50,300,0,3,2,2,R,"Example 6"↵
     * P1↵
     * Simple Expressions in Data Fields
     * An advanced function of the A command allows addition and subtraction to be performed on
     * constant and variable values in flash printers.
     * Syntax Ap 1 ,p 2 ,p 3 ,p 4 ,p 5 ,p 6 ,p 7 ,”DATA”[p 8 p 9 p 10 ...]
     * Parameters This table identifies the parameters for this format: 14245L-003 Rev. A
     * Parameters Details
     * p 1 through p 7, ”DATA” See the first page of the A command on page 43.
     * p 8 = Required variable
     * data field number Must be a variable data field number, such as V00, V01, and
     * so forth.
     * p 9 = Required operator Accepted Values: + or –
     * p 10 = Required Variable data field number or constant value.
     * Accepted Values:
     * Constant = 0 to 2147483647
     * Variable = 0 to 2147483647
     * Result = -2147483648 to 2147483647
     * • The expression must start with a variable field.
     * • The character field length defined for the first variable in
     * the expression will be used to format the result. If the
     * result is of a greater length than the defined character
     * length, then the result field will contain ‘X’s.
     * • A syntax error will be generated during form storage if the
     * constant value is too large.
     * • If an error occurs during the evaluation of the expression,
     * the resultant field will be filled with ‘X’s.
     */
    public function drawText(
        $xIni = 0,
        $yIni = 0,
        $rotation = 0,
        $font = 1,
        $hMult = 1,
        $vMult = 1,
        $reverse = 'N',
        $data = ''
    ) {
        $font = substr($font, 0, 1);
        if ($reverse != 'N') {
            $reverse = 'R';
        }
        if ($font == 6 || $font == 7) {
            $data = preg_replace("/[^0-9 ,-.]/", "", $data); //numeros somente
        }
        $strSearch = array('"', "\\");
        $strReplace = array('\"','');
        $data = str_replace($strSearch, $strReplace, $data);
        $data = iconv("UTF-8", self::$charset."//TRANSLIT", $data);
        $rotOp = array(
          0 => 0, //normal (no rotation)
          90 => 1, //90 degrees
          180 => 2, //180 degrees
          270 => 3 //270 degrees
        );
        $command = 'A'
            . self::zMm2dots($xIni).','
            . self::zMm2dots($yIni).','
            . $rotOp[$rotation].','
            . $font.','
            . $hMult.','
            . $vMult.','
            . $reverse.','
            . "\"$data\""
            . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * deleteStoredGraphics
     * Use this command to delete graphics from printer memory
     * @return string
     */
    public function deleteStoredGraphics($name = '')
    {
        if ($name != '') {
            $command = 'GK"'.$name.'"'.self::CRLF.'GK"'.$name.'"'.self::CRLF;
        } else {
            $command = 'GK"*"'.self::CRLF;
        }
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * deleteSoftFont
     * This command is used to delete soft fonts from memory.
     * @param string $name
     * @return string
     */
    public function deleteSoftFont($name = '')
    {
        $name = substr($name, 0, 1);
        $name = preg_replace("/[^a-zA-Z]/", '', $name);
        if ($name != '') {
            $command = 'EK"'.$name.'"'.self::CRLF;
        } else {
            $command = 'EK"*"'.self::CRLF;
        }
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Use this command to select the print density. The density command controls
     * the amount of heat produced by the print head. More heat will produce a darker image. Too
     * much heat can cause the printed image to distort.
     * Accepted Values: 0–15
     * Default Value:
     *       2443 (Orion) and 2844: 10
     *       G-Series: 8
     *       All other printers: 7
     * Note • 0 is the lightest print and 15 is the darkest.
     * The density and speed commands can dramatically affect print quality. Changes in the
     * speed setting typically require a change to the print density.
     * @param int $density
     * @return string
     */
    public function setDensity($density = 7)
    {
        if ($density < 1 || $density > 15) {
            $density = 7;
        }
        $command = 'D'. $density . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Speed Select
     * Use this command to select the print speed.
     * Mobile printers, such as the TR 220, ignore this command and automatically set speed to
     * optimize battery use.
     * p1 = Speed select value 0-6 model depends
     * @param type $speed
     */
    public function setSpeed($speed = 3)
    {
        if ($speed < 0 || $speed > 6) {
            $speed = 3;
        }
        $command = 'S'. $speed . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * Cut Immediate
     * This command allows the printer to initiate an immediate media cut without a
     * form print operation. The printer must have the cutter option installed.
     *   • The C command – Cut Immediate can not be used inside of a form.
     *   • The initial character C in a command string is used for both the Cut Immediate (C) and
     * Counter Command function (Cp1) which can only be used within a form. The Cut
     * Immediate Command (C) can not be used in a form.
     * Mobile printers, such as the TR 220, ignore this command.
     * Note • Use only cut label liner (backing) or tag stock. Label adhesive will build up on the
     *        cutter blade and cause the cutter to operate poorly or jam if the labels are cut along with the
     *        label liner.
     * Use the C command - Cut Immediate 5 times without media loaded, to perform a self
     * cleaning of the cutter blade.
     */
    public function cutImmediate()
    {
        $command = 'C' . self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * clearPrinterBuffer
     * This command clears the image buffer prior to building a new label image.
     * Considerations:
     *      • Do not use the N command within stored forms.
     *      • All printer configuration commands should be issued prior to issuing the N command to
     *        begin building the image for printing within the image buffer.
     *      • Always send a Line Feed (LF) prior to the N command to ensure that previous data in the
     *        command buffer has cleared and the printer is initialized and ready to accept commands.
     * @return string
     */
    public function clearPrinterBuffer()
    {
        if ($this->device->buffer == '') {
            $this->device->buffer = self::CRLF;
        }
        $command = 'N'.self::CRLF;
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * qrCode
     * @param number $xPos
     * @param number $yPos
     * @param int $model
     * @param int $scale
     * @param string $errLevel
     * @param string $data
     * @return string
     */
    public function drawQRCode($xPos = 45, $yPos = 5.6, $model = 2, $scale = 6, $errLevel = 'M', $data = '')
    {
        $command = 'b'
            . self::zMm2dots($xPos)
            . ','
            . self::zMm2dots($yPos)
            . ',Q'
            . ',m' . $model
            . ',s' . $scale
            . ',e' . $errLevel
            . ',"' . $data . '"';
        $this->device->buffer .= $command;
        return $command;
    }
    
    /**
     * zMm2dots
     * Converte milimetros para dots 
     * @param number $vMM
     * @return int
     */
    protected static function zMm2dots($vMM = 1)
    {
        return (int) round($vMM * $this->dpmm, 0);
    }
    
    /**
     * zDots2mm
     * Converte dots para milimetros
     * @param int $dots
     * @return number
     */
    protected static function zDots2mm($dots = 100)
    {
        return $dots/$this->dpmm;
    }
}

<?php
/**
 */

namespace ThermalPrint\Common;

use Endroid\QrCode\QrCode;
use \Exception;

class Graphics
{
    /**
     * Dados de imagem em linhas e colunas (1 preto e 0 para branco)
     * @var string
     */
    private static $imgData;
    /**
     * Dados de imagem raster 
     * @var string 
     */
    private static $imgRasterData;
    /**
     * Altura da imagem
     * @var int
     */
    private static $imgHeight;
    /**
     * Largura da imagem
     * @var int
     */
    private static $imgWidth;
    /**
     * GD Object
     * @var object 
     */
    public static $img;
    
    /**
     * 
     * 
     */
    public function __construct($filename = '')
    {
        
        self::$imgRasterData = null;
        self::$imgData = null;
        $this->loadFileImg($filename);
    }
    
    /**
     * loadImg
     * Carrega a imagem do arquivo indicado
     * @param string $filename
     * @return null
     * @throws Exception
     */
    public function loadFileImg($filename = '')
    {
        if (! is_file($filename)) {
            return;
        }
        if (!is_readable($filename)) {
            throw new Exception("Não é possivel ler esse arquivo '$filename' Permissões!!");
        }
        $tipo = self::zIdentifyImg($filename);
        if ($tipo == 'BMP') {
            self::$img = self::zImagecreatefrombmp($filename);
        } else {
            $func = 'imagecreatefrom' . strtolower($tipo);
            if (! function_exists($func)) {
                throw new Exception("Não é possivel usar ou tratar esse tipo de imagem, com GD");
            }
            self::$img = $func($filename);
            if (!self::$img) {
                throw new Exception("Falhou ao carregar a imagem '$filename'.");
            }
        }
        self::$imgHeight = imagesy(self::$img);
        self::$imgWidth = imagesx(self::$img);
        self::zBWImg();
        self::toRasterFormat();
        return;
    }
    
    /**
     * 
     * @param int $size
     * @param type $padding 
     * @param type $errCorretion LOW, MEDIUM, QUARTILE, HIGH
     * @param string $imageType PNG, GIF, JPEG, WBMP
     * @param string $dataText dados do QRCode
     */
    public function createQRCodeImg(
        $width = 200,
        $padding = 10,
        $errCorretion = 'low',
        $dataText = 'NADA NADA NADA'
    ) {
        if ($dataText == '') {
            return;
        }
        $qrCode = new QrCode();
        $qrCode->setText($dataText)
               ->setImageType('png')
               ->setSize($width)
               ->setPadding($padding)
               ->setErrorCorrection($errCorretion)
               ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
               ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
               ->setLabel('')
               ->setLabelFontSize(8);
        
        self::$img = $qrCode->getImage();
        self::$imgHeight = imagesy(self::$img);
        self::$imgWidth = imagesx(self::$img);
        self::zBWImg();
        return self::toRasterFormat();
    }
    
    public static function imgScale()
    {
        
    }
    
    /**
     * Altura da imagem em pixels
     * @return int
     */
    public static function getHeight()
    {
        return (int) self::$imgHeight;
    }

    /**
     * largura da imagem em pixels
     * @return int
     */
    public static function getWidth()
    {
        return self::$imgWidth;
    }
    
    /**
     * Numero de bytes que representa a altura da imagem
     * @return int 
     */
    public static function getHeightBytes()
    {
        return (int)((self::$imgHeight + 7) / 8);
    }
    
    /**
     * Numero de bytes que representa a largura da imagem
     * @return int
     */
    public static function getWidthBytes()
    {
        return (int)((self::$imgWidth + 7) / 8);
    }
    
    /**
     * Output the image in raster (row) format.
     * This can result in padding on the right of the image, 
     * if its width is not divisible by 8.
     * 
     * @throws Exception Where the generated data is unsuitable for the printer (indicates a bug or oversized image).
     * @return string The image in raster format.
     */
    public static function toRasterFormat()
    {
        if (self::$imgRasterData != null) {
            return self::$imgRasterData;
        }
        // Loop through and convert format
        $widthPixels = self::getWidth();
        $heightPixels = self::getHeight();
        $widthBytes = self::getWidthBytes();
        $xPos = $yPos = $bit = $byte = $byteVal = 0;
        $data = str_repeat("\0", $widthBytes * $heightPixels);
        do {
            $byteVal |= (int) self::$imgData[$yPos * $widthPixels + $xPos] << (7 - $bit);
            $xPos++;
            $bit++;
            if ($xPos >= $widthPixels) {
                $xPos = 0;
                $yPos++;
                $bit = 8;
                if ($yPos >= $heightPixels) {
                    $data[$byte] = chr($byteVal);
                    break;
                }
            }
            if ($bit >= 8) {
                $data[$byte] = chr($byteVal);
                $byteVal = 0;
                $bit = 0;
                $byte++;
            }
        } while (true);
        if (strlen($data) != (self::getWidthBytes() * self::getHeight())) {
            throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
        }
        self::$imgRasterData = $data;
        return self::$imgRasterData;
    }

    /**
     * zBWImg
     * Converte a imagem em preto e branco
     */
    protected static function zBWImg()
    {
        self::$imgData = str_repeat("\0", self::$imgHeight * self::$imgWidth);
        for ($yPos = 0; $yPos < self::$imgHeight; $yPos++) {
            for ($xPos = 0; $xPos < self::$imgWidth; $xPos++) {
                $cols = imagecolorsforindex(self::$img, imagecolorat(self::$img, $xPos, $yPos));
                $greyness = (int)($cols['red'] + $cols['red'] + $cols['blue']) / 3;
                $black = (255 - $greyness) >> (7 + ($cols['alpha'] >> 6));
                self::$imgData[$yPos * self::$imgWidth + $xPos] = $black;
            }
        }
    }
    
    /**
     * zIdentifyImg
     * @param string $filename
     * @return string
     */
    private static function zIdentifyImg($filename)
    {
        $imgtype = exif_imagetype($filename);
        switch ($imgtype) {
            case 1:
                $typo = 'GIF';
                break;
            case 2:
                $typo = 'JPEG';
                break;
            case 3:
                $typo = 'PNG';
                break;
            case 6:
                $typo = 'BMP';
                break;
            case 15:
                $typo = 'WBMP';
                break;
            default:
                $typo = 'none';
        }
        return $typo;
    }
    
    /**
     * imagecreatefrombmp
     * Permite o carregamente e manipulação de imagens BMP normais
     * @param string $filename
     * @return GD object
     */
    private static function zImagecreatefrombmp($filename)
    {
        $file = fopen($filename, "rb");
        $read = fread($file, 10);
        while (!feof($file) && $read != "") {
            $read .= fread($file, 1024);
        }
        $temp = unpack("H*", $read);
        $hex = $temp[1];
        $header = substr($hex, 0, 104);
        $body = str_split(substr($hex, 108), 6);
        if (substr($header, 0, 4) == "424d") {
            $header = substr($header, 4);
            $header = substr($header, 32);
            $width = hexdec(substr($header, 0, 2));
            $header = substr($header, 8);
            $height = hexdec(substr($header, 0, 2));
            unset($header);
        }
        $xPos = 0;
        $yPos = 1;
        $image = imagecreatetruecolor($width, $height);
        foreach ($body as $rgb) {
            $r = hexdec(substr($rgb, 4, 2));
            $g = hexdec(substr($rgb, 2, 2));
            $b = hexdec(substr($rgb, 0, 2));
            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $xPos, $height-$yPos, $color);
            $xPos++;
            if ($xPos >= $width) {
                $xPos = 0;
                $yPos++;
            }
        }
        return $image;
    }
}

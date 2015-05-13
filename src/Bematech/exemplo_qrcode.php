<?php

$msg = 'https://www.sefaz.rs.gov.br/NFCE/NFCE-COM.aspx?chNFe=43141006354976000149650540000086781171025455&nVersao=100&tpAmb=2&dhEmi=323031342d31302d33305431353a33303a32302d30323a3030&vNF=0.10&vICMS=0.00&digVal=682f4d6b6b366134416d6f7434346d335a386947354f354b6e50453d&cIdToken=000001&cHashQRCode=771A7CE8C50D01101BDB325611F582B67FFF36D0';

$msgLen = (int) strlen($msg);
$iTam1 = $msgLen;
$item2 = 0;
if ($msgLen > 255) {
    $iTam1 = (int) $msgLen % 255;
    $iTem2 = (int) $msgLen / 255;
}

$centraliza = chr(27).chr(97).chr(1);

$qrcode = chr(29) . chr(107) . char(81)
          . chr(2) . chr(12) . chr($msgLen) . chr(1) // aqui é a dimenção do QR CODE
          . chr($iTam1)  // resto da divisão correspondente ao tamanho do texto / 255
          . chr($iTam2)   // divisão correspondente ao tamanho do texto
          . $msg;    // aqui começa o texto

$cutcommand = chr(27) . chr(119);   // acionamento da Guilhotina.

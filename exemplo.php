<?php
header('Content-type: text/html; charset=UTF-8');

include_once 'arrecadacao.class.php';

$arrec = new Arrecadacao();
$arrec->setLinhaDigitavel('836400000003309400481003966736718018000502717721');

$arrec2 = new Arrecadacao();



print '<pre>';
print_r($arrec->getDadosCodigoBarras());

echo $arrec->getLinhaDigitavel() . "\r\n";

echo $arrec->getCodigoBarras() . "\r\n";

echo $arrec2->getCodigoBarras() . "\r\n"; // lançará exceção


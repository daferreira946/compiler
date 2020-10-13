<?php


use Compiler\src\Decoder;
use Compiler\src\Lexic;
use Compiler\src\Syntax;

require '../vendor/autoload.php';

$openPre = "<pre>";
$closePre = "</pre>";

$decoder = new Decoder();
$config = $decoder->getConfig();

$file = '../uploads/algoritmo.txt';

$lexic = new Lexic($config, $file);
$parsed = $lexic->getContent();
$token = $lexic->getTokenCode();

if ($token === false) {
    echo "Erro, <a href='../index.php'>clique aqui</a>  para voltar";
}

$lexic->lexicAnalyzer($token);

if ($lexic->getErrors() !== false) {
    $errors = $lexic->getErrors();
    echo $openPre . var_dump($errors) . $closePre;
    die();
}

$lexicTable = $lexic->getLexicTable();

$lexicIterator = $lexic->getLexicIterator();
$lexicIteratorIndex = $lexic->getLexicIteratorIndex();

echo "<table style='border: black solid;padding: 10px'>";
echo "<h1>Tabela Sintática</h1>";
foreach ($lexicTable as $line => $lines) {
    foreach ($lines as $column => $values) {
        foreach ($values as $token => $value) {
            echo "<tr style='border: black solid;padding: 10px'>";
            echo "<td style='border: black solid;padding: 10px'>";
            echo "Position $line : $column";
            echo "</td>";
            echo "<td style='border: black solid;padding: 10px'>";
            echo " |$token| => |$value|";
            echo "</td>";
            echo "</tr>";
        }
    }
}
echo "</table>";

$syntax = new Syntax($lexicIterator, $lexicIteratorIndex);
echo "<h1>Sintático</h1>";
$syntax->syntaxAnalyser();

<?php

use Compiler\src\Lexical;
use Compiler\src\Syntax;

require '../vendor/autoload.php';

$openPre = "<pre>";
$closePre = "</pre>";

$file = '../uploads/algoritmo.txt';

$lexical = new Lexical($file);
$content = $lexical->getContent();
$token = $lexical->getTokenCode();

if ($token === false) {
    echo "Erro, <a href='../index.php'>clique aqui</a>  para voltar";
}

$lexical->lexicalAnalyzer($token);

if ($lexical->getErrors() !== false) {
    $errors = $lexical->getErrors();
    echo $openPre . var_dump($errors) . $closePre;
    die();
}

$lexicalTable = $lexical->getLexicalTable();

$lexicalIterator = $lexical->getLexicalIterator();
$lexicalIteratorIndex = $lexical->getLexicalIteratorIndex();

echo "<table style='border: black solid;padding: 10px'>";
echo "<h1>Tabela Sintática</h1>";
foreach ($lexicalTable as $line => $lines) {
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

$syntax = new Syntax($lexicalIterator, $lexicalIteratorIndex);
echo "<h1>Sintático</h1>";
$syntax->syntaxAnalyser();

<?php


use Compiler\src\Decoder;
use Compiler\src\Lexic;

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
for ($i = 0; $i < count($lexicTable); $i++) {
    for ($a = 0; $a < count($lexicTable[$i]); $a++) {
        foreach ($lexicTable[$i][$a] as $token => $value) {
            echo "<tr style='border: black solid;padding: 10px'>";
                echo "<td style='border: black solid;padding: 10px'>";
                    echo "Position $i : $a";
                echo "</td>";
                echo "<td style='border: black solid;padding: 10px'>";
                    echo " |$token| => |$value|";
                echo "</td>";
            echo "</tr>";
        }
    }
}
echo "</table>";

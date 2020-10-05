<?php

namespace Compiler\src;

require '../vendor/autoload.php';

$name = basename('algoritmo.txt');
$origin = $_FILES["file"]["tmp_name"];
$uploader = new Uploader($name);


if ($uploader->moveFile($origin)) {
    echo "O algoritmo foi recebido com sucesso.";
    echo "<br>";
    echo "Para ver a tabela léxica <a href='lexicAnalyser.php'>clique aqui</a>";
} else {
    echo "Desculpe, houve um erro ao fazer o upload do seu arquivo.<br>";
    echo "<br><a href='../index.php'>voltar</a>";
}

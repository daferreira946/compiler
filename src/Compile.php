<?php


namespace Compiler\src;

class Compile
{
    private Lexical $lexical;
    private Syntax $syntax;

    public function __construct(string $filePath)
    {
        $this->lexical = new Lexical($filePath);
        $this->lexicalAnalyzer();
        $this->syntax = new Syntax($this->lexical->getLexicalIterator(), $this->lexical->getLexicalIteratorIndex());
        $this->syntaxAnalyzer();
    }

    private function lexicalAnalyzer()
    {
        $lexicalAnalyzer = $this->lexical->printLexicTable();
        if ($lexicalAnalyzer === false) {
            echo "Erro no léxico, <a href='../index.php'>clique aqui</a>  para voltar <br>";
            die();
        }
    }

    private function syntaxAnalyzer()
    {
        $syntaxAnalyzer = $this->syntax->syntaxAnalyser();
        if ($syntaxAnalyzer === false) {
            echo "Erro no sintático, <a href='../index.php'>clique aqui</a>  para voltar <br>";
            die();
        }
    }


}
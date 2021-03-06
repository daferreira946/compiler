<?php

namespace Compiler\src;

use ArrayIterator;

class Lexical
{
    private array $config;
    private array $parsed;
    private array $trimmed;
    private array $tokens;
    private array $lexicalTable;
    private array $lexicalIndexTable;
    private string $error;

    public function __construct(string $file)
    {
        $json = file_get_contents("../config.json");
        $this->config = json_decode($json, true);
        $this->parsed = file($file);
        $this->trimming();
    }

    private function trimming(): void
    {
        $parsed = $this->parsed;
        foreach ($parsed as $toTrim) {
            //Tirando chars vazios, enters, tabs e etc
            $trimmed = trim($toTrim, " \t\n\r\0\x0B");
            //Adicionando espaços entre os símbolos para ajudar a separar os chars
            foreach ($this->config['symbols'] as $symbols) {
                foreach ($symbols as $symbol) {
                    $replaceItem = " $symbol ";
                    //Checando se o símbolo está no array aparado
                    if (strpos($trimmed, $symbol) !== false) {
                        //Substituindo o símbolo pelo o " símbolo "
                        $trimmed = str_replace($symbol, $replaceItem, $trimmed);
                    }
                }
            }
            //Aqui eu retiro os espaços adicionados
            $trimmed = str_replace('  ', ' ', $trimmed);
            //Aqui eu passo o elemento aparado para o array
            $this->trimmed[] = $trimmed;
        }
    }

    /**
     * @return false|array
     */
    private function getTokenCode()
    {
        $pattern = '/ /';
        for ($line = 0; $line < count($this->trimmed); $line++) {
            //Deixando tudo minúsculo
            $lowerLine = strtolower($this->trimmed[$line]);
            //Explodindo a string de acordo com o padrão
            $splitted = preg_split($pattern, $lowerLine);
            //Varrer a array resultante da explosão
            for ($line2 = 0; $line2 < count($splitted); $line2++) {
                //Checando se a posição está settada e se é igual a :
                if (isset($splitted[$line2]) && $splitted[$line2] === ':') {
                    //Checando se o próximo é igual a =
                    if ($splitted[$line2+1] === '=') {
                        //Concatenando em :=
                        $splitted[$line2] = $splitted[$line2] . $splitted[$line2+1];
                        //Tirando o próximo index
                        unset($splitted[$line2+1]);
                    }
                }
                //Checando se a posição tá settada e se é igual a > ou <
                if (isset($splitted[$line2]) && ($splitted[$line2] === '>' | $splitted[$line2] === '<')) {
                    //Checando se o próximo é igual a =
                    if ($splitted[$line2+1] === '=') {
                        //Concatenando em <= ou >=
                        $splitted[$line2] = $splitted[$line2] . $splitted[$line2+1];
                        //Tirando o próximo index
                        unset($splitted[$line2+1]);
                    }
                }
                //Checando se a posição está settada e se é igual a .
                if (isset($splitted[$line2]) && $splitted[$line2] === '.') {
                    //Checando se o anterior é diferente a "end"
                    if ($splitted[$line2-1] !== 'end') {
                        //Concatenando em N1.N2
                        $splitted[$line2] = $splitted[$line2-1] . $splitted[$line2] . $splitted[$line2+1];
                        //Tirando o próximo index
                        unset($splitted[$line2+1]);
                        unset($splitted[$line2-1]);
                    }
                }
                //Checando se a posição ta settada e se é string vazia
                if (isset($splitted[$line2]) &&
                    ((string)$splitted[$line2] === '' |
                        empty((string)$splitted[$line2]) |
                        (string)$splitted[$line2] === "" |
                        strlen($splitted[$line2]) === 0)) {
                    //Tirando a posição que contém string vazia
                    unset($splitted[$line2]);
                }
            }

            $tokens[$line] = $splitted;
        }

        //Checando se está vazio
        if (empty($tokens)) {
            return false;
        }
        $this->tokens = $tokens;
        return true;
    }

    private function lexicalAnalyzer(): bool
    {
        $getSuccess = $this->getTokenCode();
        if ($getSuccess === false) {
            echo "Sem tokens ";
            return false;
        }

        $tokens = $this->tokens;

        $comment = false;
        foreach ($tokens as $line => $lineContent) {
            foreach ($lineContent as $column => $value) {
                if ($value === '{') {
                    $comment = true;
                }

                $word = $this->word($value);
                $bool = $this->bool($value);
                $symbols = $this->symbols($value);
                $variables = $this->variables($value);
                $unknown = true;

                if ($comment === true) {
                    $word = false;
                    $bool = false;
                    $symbols = false;
                    $variables = false;
                    $unknown = false;
                }

                if ($word !== false) {
                    $this->lexicalTable[$line][$column][$word] = $value;
                    $this->setLexicalIndexTable($line, $value);
                    $bool = false;
                    $symbols = false;
                    $variables = false;
                    $unknown = false;
                }
                if ($bool !== false) {
                    $this->lexicalTable[$line][$column][$bool] = $value;
                    $this->setLexicalIndexTable($line, $value);
                    $symbols = false;
                    $variables = false;
                    $unknown = false;
                }
                if ($symbols !== false) {
                    $this->lexicalTable[$line][$column][$symbols] = $value;
                    $this->setLexicalIndexTable($line, $value);
                    $variables = false;
                    $unknown = false;
                }
                if ($variables !== false) {
                    $this->lexicalTable[$line][$column][$variables] = $value;
                    $this->setLexicalIndexTable($line, $value);
                    $unknown = false;
                }
                if ($value === '') {
                    $unknown = false;
                }
                if ($unknown) {
                    $this->setError($value);
                    return false;
                }

                if ($value === '}') {
                    $comment = false;
                }
            }
        }
        return true;
    }

    /**
     * @return false|array
     */
    private function getLexicalTable()
    {
        $lexicalAnalyzer = $this->lexicalAnalyzer();
        if ($lexicalAnalyzer === false) {
            return false;
        }

        return $this->lexicalTable;
    }

    private function setLexicalIndexTable(int $line, string $value): void
    {
        $column = strpos($this->parsed[$line], $value)+1;
        $line = $line + 1;
        $this->lexicalIndexTable[] = "Linha: $line Coluna: $column.";
    }

    public function printLexicalTable()
    {
        $lexicalTable = $this->getLexicalTable();
        if ($lexicalTable === false) {
            echo $this->error;
            return false;
        }

        echo "<table style='border: black solid;padding: 10px'>";
        echo "<h1>Tabela Léxica</h1>";
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
        return true;
    }

    public function getLexicalIterator(): ArrayIterator
    {
        $lexicalTable = [];

        foreach ($this->lexicalTable as $line => $lineContent) {
            foreach ($lineContent as $column => $values) {
                //Checando se o index está settado
                if (isset($this->lexicalTable[$line][$column])) {
                    foreach ($values as $token => $value) {
                        $lexicalTable[][$token] = $value;
                    }
                }
            }
        }

        return new ArrayIterator($lexicalTable);
    }

    public function getLexicalIteratorIndex(): ArrayIterator
    {
        return new ArrayIterator($this->lexicalIndexTable);
    }

    /**
     * @param $token
     * @return false|string
     */
    private function word($token)
    {
        foreach ($this->config['words'] as $word) {
            if ($token === $word) {
                return $word;
            }
        }

        return false;
    }

    /**
     * @param $token
     * @return false|string
     */
    private function bool($token)
    {
        foreach ($this->config['booleans'] as $bool) {
            if ($token === $bool) {
                return $bool;
            }
        }

        return false;
    }

    /**
     * @param $token
     * @return false|string
     */
    private function symbols($token)
    {
        foreach ($this->config['symbols'] as $symbols) {
            foreach ($symbols as $symbol) {
                if ($token === $symbol) {
                    return $symbol;
                }
            }
        }

        return false;
    }

    /**
     * @param $token
     * @return false|string
     */
    private function variables($token)
    {
        if (preg_match($this->config['variables']['id'], $token)) {
            return 'id';
        }

        if (preg_match($this->config['variables']['real'], $token)) {
            return 'real';
        }

        if (preg_match($this->config['variables']['integer'], $token)) {
            return 'integer';
        }


        return false;
    }

    private function setError(string $unknownChar)
    {
        foreach ($this->parsed as $line => $value) {
            $value = strtolower($value);
            $column = strpos($value, $unknownChar);
            if ($column !== false) {
                $column++;
                $line++;
                $position = "Linha: [$line] Coluna: [$column]";
            }
        }
        if (!empty($position)) {
            $this->error = "Erro 01, tipo léxico = $unknownChar não reconhecido. " . $position . "<br>";
        }
    }
}

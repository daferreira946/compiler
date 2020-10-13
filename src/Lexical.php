<?php

namespace Compiler\src;

use ArrayIterator;

class Lexical
{
    private array $config;
    private array $parsed;
    private array $trimmed;
    private array $errorMessage;
    private array $lexicalTable;

    public function __construct(string $file)
    {
        $json = file_get_contents("../config.json");
        $this->config = json_decode($json, true);
        $this->parsed = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->trimming();
    }

    private function trimming(): void
    {
        foreach ($this->parsed as $toTrim) {
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
    public function getTokenCode()
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


            $token[$line] = $splitted;
        }

        //Checando se está vazio
        if (empty($token)) {
            return false;
        }

        return $token;
    }

    /**
     * @return false|array
     */
    public function getErrors()
    {
        if (!isset($this->errorMessage)) {
            return false;
        }
        return $this->errorMessage;
    }

    public function getContent(): array
    {
        return $this->trimmed;
    }

    public function lexicalAnalyzer(array $tokens)
    {
        foreach ($tokens as $line => $lineContent) {
            foreach ($lineContent as $column => $value) {
                $token = $value;

                $word = $this->word($token);
                $bool = $this->bool($token);
                $symbols = $this->symbols($token);
                $variables = $this->variables($token);
                $unknown = true;
                if ($word !== false) {
                    $this->lexicalTable[$line][$column][$word] = $token;
                    $bool = false;
                    $symbols = false;
                    $variables = false;
                    $unknown = false;
                }
                if ($bool !== false) {
                    $this->lexicalTable[$line][$column][$bool] = $token;
                    $symbols = false;
                    $variables = false;
                    $unknown = false;
                }
                if ($symbols !== false) {
                    $this->lexicalTable[$line][$column][$symbols] = $token;
                    $variables = false;
                    $unknown = false;
                }
                if ($variables !== false) {
                    $this->lexicalTable[$line][$column][$variables] = $token;
                    $unknown = false;
                }
                if ($value === '') {
                    $unknown = false;
                }
                if ($unknown) {
                    $this->errorMessage[] = "Erro léxico = $token não reconhecido, na linha $line coluna $column";
                }
            }
        }
    }

    public function getLexicalTable(): array
    {
        return $this->lexicalTable;
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
        $lexicalIndexTable = [];
        foreach ($this->lexicalTable as $line => $lineContent) {
            foreach ($lineContent as $column) {
                $lexicalIndexTable[] = "Linha: [$line] Coluna: [$column]";
            }
        }

        return new ArrayIterator($lexicalIndexTable);
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
        
        if (preg_match($this->config['variables']['integer'], $token)) {
            return 'integer';
        }

        str_replace('.', $this->config['variables']['real'], '\.');
        if (preg_match($this->config['variables']['real'], $token)) {
            return 'real';
        }

        return false;
    }
}

<?php

namespace Compiler\src;

use ArrayIterator;
use ArrayObject;

require '../vendor/autoload.php';

class Lexic
{
    private array $config;
    private array $parsed;
    private array $trimmed;
    private array $errorMessage;
    private array $lexicTable;

    public function __construct(array $config, string $file)
    {
        $this->config = $config;
        $this->parsed = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->trimmed();
    }

    public function getTokenCodigo()
    {
        $pattern = '\' |;\'';
        for ($line = 0; $line < count($this->trimmed); $line++) {
            $token[$line] = preg_split($pattern, strtolower($this->trimmed[$line]));
            if ($token[$line][count($token[$line])-1] == '') {
                $token[$line][count($token[$line])-1] = ';';
            }
        }

        if (!empty($token)) {
            return $token;
        }

        return false;
    }

    public function getErrors()
    {
        if (isset($this->errorMessage)) {
            return $this->errorMessage;
        }

        return false;
    }

    public function getContent(): array
    {
        return $this->trimmed;
    }

    public function getLexicTable(): array
    {
        return $this->lexicTable;
    }

    public function getLexicIterator(): ArrayIterator
    {
        $lexicArrayObject = new ArrayObject();

        for ($line = 0; $line < count($this->lexicTable); $line++) {
            for ($column = 0; $column < count($this->lexicTable[$line]); $column++) {
                $lexicArrayObject->append($this->lexicTable[$line][$column]);
            }
        }


        return $lexicArrayObject->getIterator();
    }

    public function getLexicIteratorIndex(): ArrayIterator
    {
        $lexicIndexArrayObject = new ArrayObject();

        for ($line = 0; $line < count($this->lexicTable); $line++) {
            for ($column = 0; $column < count($this->lexicTable[$line]); $column++) {
                $lexicIndexArrayObject->append("Linha: [$line] Coluna: [$column]");
            }
        }

        return $lexicIndexArrayObject->getIterator();
    }

    private function trimmed(): void
    {
        foreach ($this->parsed as $toTrim) {
            $this->trimmed[] = trim($toTrim, " \t\n\r\0\x0B");
        }
    }

    public function lexicAnalyzer(array $tokens)
    {
        for ($line = 0; $line < count($tokens); $line++) {
            for ($column = 0; $column < count($tokens[$line]); $column++) {
                $token = $tokens[$line][$column];
                $word = $this->word($token);
                $bool = $this->bool($token);
                $symbols = $this->symbols($token);
                $variables = $this->variables($token);
                $unknow = true;
                if ($word !== false) {
                    $this->lexicTable[$line][$column][$word] = $token;
                    $bool = false;
                    $symbols = false;
                    $variables = false;
                    $unknow = false;
                }
                if ($bool !== false) {
                    $this->lexicTable[$line][$column][$bool] = $token;
                    $symbols = false;
                    $variables = false;
                    $unknow = false;
                }
                if ($symbols !== false) {
                    $this->lexicTable[$line][$column][$symbols] = $token;
                    $variables = false;
                    $unknow = false;
                }
                if ($variables !== false) {
                    $this->lexicTable[$line][$column][$variables] = $token;
                    $unknow = false;
                }
                if ($unknow) {
                    $this->errorMessage[] = "Erro léxico = $token não reconhecido, na linha $line coluna $column";
                }
            }
        }
    }

    private function word($token)
    {
        foreach ($this->config['words'] as $word) {
            if ($token == $word) {
                return $word;
            }
        }

        return false;
    }

    private function bool($token)
    {
        foreach ($this->config['booleans'] as $bool) {
            if ($token == $bool) {
                return $bool;
            }
        }

        return false;
    }

    private function symbols($token)
    {
        foreach ($this->config['symbols'] as $symbols) {
            foreach ($symbols as $symbol) {
                if ($token == $symbol) {
                    return $symbol;
                }
            }
        }

        return false;
    }

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

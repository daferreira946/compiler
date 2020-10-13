<?php

namespace Compiler\src;

use ArrayIterator;

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

    /**
     * @return false|array
     */
    public function getTokenCode()
    {
        $pattern = '/ /';
        for ($line = 0; $line < count($this->trimmed); $line++) {
            $splitted = preg_split($pattern, strtolower($this->trimmed[$line]));
            for ($line2 = 0; $line2 < count($splitted); $line2++) {
                if (isset($splitted[$line2]) && $splitted[$line2] === ':') {
                    if ($splitted[$line2+1] === '=') {
                        $splitted[$line2] = $splitted[$line2] . $splitted[$line2+1];
                        unset($splitted[$line2+1]);
                    }
                }
                if (isset($splitted[$line2]) && ($splitted[$line2] === '>' | $splitted[$line2] === '<')) {
                    if ($splitted[$line2+1] === '=') {
                        $splitted[$line2] = $splitted[$line2] . $splitted[$line2+1];
                        unset($splitted[$line2+1]);
                    }
                }
                if (isset($splitted[$line2]) &&
                    ((string)$splitted[$line2] === '' |
                        empty((string)$splitted[$line2]) |
                        (string)$splitted[$line2] === "" |
                        strlen($splitted[$line2]) === 0)) {
                    unset($splitted[$line2]);
                }
            }
            $token[$line] = $splitted;
        }

        if (!empty($token)) {
            return $token;
        }

        return false;
    }

    /**
     * @return false|array
     */
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
        $lexicTable = [];

        foreach ($this->lexicTable as $line => $lines) {
            foreach ($lines as $column => $values) {
                if (isset($this->lexicTable[$line][$column])) {
                    foreach ($values as $key => $value) {
                        $lexicTable[][$key] = $value;
                    }
                }
            }
        }

        return new ArrayIterator($lexicTable);
    }

    public function getLexicIteratorIndex(): ArrayIterator
    {
        $lexicIndexTable = [];
        foreach ($this->lexicTable as $line => $lines) {
            foreach ($lines as $column => $value) {
                $lexicIndexTable[] = "Linha: [$line] Coluna: [$column]";
            }
        }

        return new ArrayIterator($lexicIndexTable);
    }

    private function trimmed(): void
    {
        foreach ($this->parsed as $toTrim) {
            $trimmed = trim($toTrim, " \t\n\r\0\x0B");
            foreach ($this->config['symbols'] as $symbols) {
                foreach ($symbols as $symbol) {
                    $replaceItem = " $symbol ";
                    if (strpos($trimmed, $symbol) !== false) {
                        $trimmed = str_replace($symbol, $replaceItem, $trimmed);
                    }
                }
            }
            $trimmed = str_replace('  ', ' ', $trimmed);
            $this->trimmed[] = $trimmed;
        }
    }

    public function lexicAnalyzer(array $tokens)
    {
        foreach ($tokens as $line => $lines) {
            foreach ($lines as $column => $value) {
                $token = $value;

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
                if ($value === '') {
                    $unknow = false;
                }
                if ($unknow) {
                    $this->errorMessage[] = "Erro léxico = $token não reconhecido, na linha $line coluna $column";
                }
            }
        }
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

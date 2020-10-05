<?php

namespace Compiler\src;

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
        for ($i = 0; $i < count($this->trimmed); $i++) {
            $token[$i] = preg_split($pattern, strtolower($this->trimmed[$i]));
            if ($token[$i][count($token[$i])-1] == '') {
                $token[$i][count($token[$i])-1] = ';';
            }
        }

        if (!empty($token)) {
            return $token;
        }
    }

    public function getErrors()
    {
        if (isset($this->errorMessage)) {
            return $this->errorMessage;
        }
    }

    public function lexicAnalyzer(array $tokens)
    {
        for ($i=0; $i < count($tokens); $i++) {
            for ($a=0; $a < count($tokens[$i]); $a++) {
                $token = $tokens[$i][$a];
                $word = $this->word($token);
                $bool = $this->bool($token);
                $symbols = $this->symbols($token);
                $variables = $this->variables($token);
                $unknow = true;
                if ($word !== false) {
                    $this->lexicTable[$i][$a][$word] = $token;
                    $bool = false;
                    $symbols = false;
                    $variables = false;
                    $unknow = false;
                }
                if ($bool !== false) {
                    $this->lexicTable[$i][$a][$bool] = $token;
                    $symbols = false;
                    $variables = false;
                    $unknow = false;
                }
                if ($symbols !== false) {
                    $this->lexicTable[$i][$a][$symbols] = $token;
                    $variables = false;
                    $unknow = false;
                }
                if ($variables !== false) {
                    $this->lexicTable[$i][$a][$variables] = $token;
                    $unknow = false;
                }
                if ($unknow) {
                    $this->errorMessage[] = "Erro léxico = $token não reconhecido, na linha $i coluna $a";
                }
            }
        }
    }

    public function getContent(): array
    {
        return $this->trimmed;
    }

    public function getLexicTable(): array
    {
        return $this->lexicTable;
    }

    private function trimmed(): void
    {
        foreach ($this->parsed as $toTrim) {
            $this->trimmed[] = trim($toTrim, " \t\n\r\0\x0B");
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

<?php

namespace Compiler\src;

use ArrayObject;

class Semantic
{
    private ArrayObject $variables;
    private array $config;

    public function __construct()
    {
        $decoder = new Decoder();
        $this->config = $decoder->getConfig();
        $this->variables = new ArrayObject();
    }

    public function setVariable(String $type, String $key, String $position): bool
    {
        $array = array(
            "type" => $type,
            "position" => $position,
            "value" => ""
        );

        if ($this->alreadyDeclared($key)) {
            echo 'Erro 6: Variável "' . $key . '" declarada em duplicidade. ' . $position . '.<br>';
            return false;
        }

        $this->variables[$key] = $array;
        return true;
    }

    private function alreadyDeclared(string $key): bool
    {
        if (array_key_exists($key, $this->variables)) {
            return true;
        }

        return false;
    }

    public function checkVariableSet(string $key, string $position)
    {
        if ($this->alreadyDeclared($key) === false) {
            echo 'Erro 4: Identificador "' . $key .'" não declarado. '. $position .'<br>';
            return false;
        }

        return true;
    }

    public function checkVariableType(string $id, string $type, string $position)
    {
        if ($this->variables[$id]["type"] !== $type) {
            echo "Erro 3: Tipos Incompatíveis. [$type] e ["
                .$this->variables[$id]["type"]. "]. "
                .$position. ".<br>";
            return false;
        }

        return true;
    }

    public function checkNotString(string $id, string $position)
    {
        if ($this->variables[$id]["type"] === "string") {
            echo "Erro 3: Tipos Incompatíveis. [integer ou real] e ["
                .$this->variables[$id]["type"]. "]. "
                .$position. ".<br>";
            return false;
        }

        return true;
    }

    public function checkExpression(ArrayObject $expression, string $position)
    {
        $expressionIterator = $expression->getIterator();
        $received = $expressionIterator->current();
        $type = $this->variables[$received]["type"];
        $expressionIterator->next();
        
        //pular o símbolo de atribuição
        $expressionIterator->next();
        $calc = "return ";
        while ($expressionIterator->valid()) {
            $calc .= $expressionIterator->current();
            $expressionIterator->next();
        }

        $calc .= ";";
        $received = eval($calc);

        if ($type === 'integer' && !is_integer($received)) {
            echo "Erro 3: Tipos Incompatíveis. [integer] e [real]. "
                .$position. "<br>";
            return false;
        }

        return true;
    }
}

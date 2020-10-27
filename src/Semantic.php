<?php

namespace Compiler\src;

use ArrayObject;

class Semantic
{
    private ArrayObject $variables;

    public function __construct()
    {
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
        //Pegando Iterator da expressão
        $expressionIterator = $expression->getIterator();
        //Pegando a variável que vai receber o resultado da expressão
        $received = $expressionIterator->current();
        //Pegando o tipo da variável que vai receber o resultado da expressão
        $type = $this->variables[$received]["type"];

        $expressionIterator->next();
        
        //Pulando o símbolo de atribuição
        $expressionIterator->next();

        //Iniciando a string de calc para retornar o valor
        $calc = "return ";

        //Concatenando toda a expressão
        while ($expressionIterator->valid()) {
            if ($this->alreadyDeclared($expressionIterator->current())) {
                $calc .= $this->variables[$expressionIterator->current()]["value"];
                $expressionIterator->next();
            } else {
                $calc .= $expressionIterator->current();
                $expressionIterator->next();
            }
        }

        //Concatenando com ; para encerrar expressão php
        $calc .= ";";

        //Resolvendo a expressão contida na string calc e passando para a variável received
        $result = eval($calc);

        //Checa se o tipo da variável é integer e se o resultado da expressão é diferente de inteiro
        //Lança o erro
        if ($type === 'integer' && !is_integer($result)) {
            echo "Erro 3: Tipos Incompatíveis. [integer] e [real]. "
                .$position. "<br>";
            return false;
        }

        $this->variables[$received]["value"] = $result;
        return true;
    }
}

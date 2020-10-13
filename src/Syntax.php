<?php


namespace Compiler\src;

use ArrayIterator;

class Syntax
{
    private string $error;
    private ArrayIterator $lexicTable;
    private ArrayIterator $lexicIndexTable;

    public function __construct(ArrayIterator $lexicTable, ArrayIterator $lexicIndexTable)
    {
        $this->lexicTable = $lexicTable;
        $this->lexicIndexTable = $lexicIndexTable;
    }

    public function syntaxAnalyser()
    {
        if (!$this->program()) {
            echo "<pre>";
            var_dump($this->getError());
            echo "</pre>";
            return $this->getError();
        }

        return true;
    }

    private function getError()
    {
        return $this->error;
    }

    private function setError(string $expected)
    {
        $this->error = 'Erro sintático: esperado ' . $expected . ', encontrado ' . $this->getLexicKey()
        . '. ' . $this->lexicIndexTable->current();
    }

    private function getLexicKey()
    {
        return key($this->lexicTable->current());
    }

    private function getLexicValue()
    {
        return $this->lexicTable->current()[$this->getLexicKey()];
    }

    private function print(string $expected)
    {
        print $expected;
        print " => ";
        print $this->getLexicKey();
        print " = ";
        print $this->getLexicValue();
        print "<br>";
    }

    /**
     * @return false|mixed
     */
    private function program()
    {
        if ($this->getLexicKey() !== 'program') {
            $this->setError('program');
            return false;
        }
        $this->print('program');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== 'id') {
            $this->setError('id');
            return false;
        }
        $this->print('id');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== ';') {
            $this->setError(';');
            return false;
        }
        $this->print(';');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $variableDeclaration = $this->variableDeclaration();
        if (!$variableDeclaration) {
            return false;
        }
        
        if ($this->getLexicKey() !== 'begin') {
            $this->setError('begin');
            return false;
        }

        $this->print('begin');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $block = $this->block();
        if (!$block) {
            return false;
        }

        if ($this->getLexicKey() !== 'end') {
            $this->setError('end');
            return false;
        }

        $this->print('end');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== '.') {
            $this->setError('.');
            return false;
        }

        $this->print('.');

        return true;
    }

    private function variableDeclaration()
    {
        while ($this->getLexicKey() === 'integer' |
            $this->getLexicKey() === 'real' |
            $this->getLexicKey() === 'string') {
            $this->print($this->getLexicKey());
            while ($this->getLexicKey() !== ';') {
                $this->lexicTable->next();
                $this->lexicIndexTable->next();
                if ($this->getLexicKey() !== 'id') {
                    $this->setError('id');
                    return false;
                }
                $this->print('id');

                $this->lexicTable->next();
                $this->lexicIndexTable->next();
                if ($this->getLexicKey() !== ',' && $this->getLexicKey() !== ';') {
                    $this->setError(', ou ;');
                    return false;
                }
                $this->print($this->getLexicKey());
            }

            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }

        return true;
    }

    private function block()
    {
        if ($this->getLexicKey() !== 'begin') {
            $this->setError('begin');
            return false;
        }

        $this->print('begin');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $command = $this->command();
        if (!$command) {
            return false;
        }

        if ($this->getLexicKey() !== 'end') {
            $this->setError('end');
            return false;
        }

        $this->print('end');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== ';') {
            $this->setError(';');
            return false;
        }

        $this->print(';');

        return true;
    }

    private function command()
    {
        $basicCommand = $this->basicCommand();
        if (!$basicCommand) {
            return false;
        }
        return true;
    }

    private function basicCommand()
    {
        if ($this->getLexicKey() === 'id') {
            $attribution = $this->attribution();
            if (!$attribution) {
                return false;
            }
        }

        if ($this->getLexicKey() === 'while' | $this->getLexicKey() === 'repeat') {
            $attribution = $this->iteration();
            if (!$attribution) {
                return false;
            }
        }
    }

    private function attribution()
    {
        $this->print('id');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== ':=') {
            $this->setError(':=');
            return false;
        }

        $this->print(':=');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $arithmeticExpression = $this->arithmeticExpression();
        if (!$arithmeticExpression) {
            return false;
        }

        if ($this->getLexicKey() !== ';') {
            $this->setError(';');
            return false;
        }

        $this->print(';');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        return true;
    }

    private function arithmeticExpression()
    {
        if ($this->getLexicKey() === '(') {
            $this->print('(');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $arithmeticExpression = $this->arithmeticExpression();
            if (!$arithmeticExpression) {
                return false;
            }

            if ($this->getLexicKey() !== ')') {
                $this->setError(')');
                return false;
            }

            $this->print(')');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            if ($this->getLexicKey() !== '+' |
                $this->getLexicKey() !== '-' |
                $this->getLexicKey() !== '*' |
                $this->getLexicKey() !== '/') {
                $this->setError('+ ou - ou * ou /');
                return false;
            }

            $this->print($this->getLexicKey());

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            if ($this->getLexicKey() !== '(') {
                $this->setError('(');
                return false;
            }

            $this->print('(');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $arithmeticExpression = $this->arithmeticExpression();
            if (!$arithmeticExpression) {
                return false;
            }

            if ($this->getLexicKey() !== ')') {
                $this->setError(')');
                return false;
            }

            $this->print(')');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            return true;
        }

        $value = $this->value();
        if (!$value) {
            return false;
        }

        if ($this->getLexicKey() === '+' |
            $this->getLexicKey() === '-' |
            $this->getLexicKey() === '*' |
            $this->getLexicKey() === '/') {
            $this->print($this->getLexicKey());

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $value = $this->value();
            if (!$value) {
                return false;
            }

            return true;
        }

        return true;
    }

    private function comment()
    {
        if ($this->getLexicValue() !== '{') {
            $this->setError('{');
            return false;
        }
        while ($this->lexicTable->valid()) {
            if ($this->getLexicValue() === "}") {
                $this->lexicTable->next();
                $this->lexicIndexTable->next();
                return true;
            }
            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }
        $this->setError('}');
        return false;
    }

    private function value()
    {
        if ($this->getLexicKey() !== "id" && $this->getLexicKey() !== "integer" && $this->getLexicKey() !== "real") {
            $this->setError("id ou integer ou real");
            return false;
        }

        $this->print($this->getLexicKey());
        $this->lexicTable->next();
        $this->lexicIndexTable->next();
        return true;
    }

    private function iteration()
    {
        if ($this->getLexicKey() === 'while') {
            $this->print('while');
            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            if ($this->getLexicKey() !== '(') {
                $this->setError('(');
                return false;
            }
            $this->print('(');
            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $relationalExpretion = $this->relationalExpression();
            if (!$relationalExpretion) {
                return false;
            }

            if ($this->getLexicKey() !== ')') {
                $this->setError(')');
                return false;
            }
            $this->print(')');
            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            if ($this->getLexicKey() !== 'do') {
                $this->setError('do');
                return false;
            }
            $this->print('do');
            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            return true;
        }
    }

    private function relationalExpression()
    {

    }
}

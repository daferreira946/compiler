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
        echo htmlspecialchars("<Program>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        $mainBlock = $this->mainBlock();
        if (!$mainBlock) {
            return false;
        }

        echo htmlspecialchars("</Program>") . '<br>';

        return true;
    }

    private function mainBlock()
    {
        echo htmlspecialchars("<Main_Block>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->lexicTable->valid() !== false) {
            if ($this->getLexicKey() !== '{') {
                $this->setError('{');
                return false;
            }

            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Main_Block>") . '<br>';
        return true;
    }

    private function variableDeclaration()
    {
        echo htmlspecialchars("<Variable_Declaration>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        echo htmlspecialchars("</Variable_Declaration>") . '<br>';
        return true;
    }

    private function block()
    {
        echo htmlspecialchars("<Block>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicKey() !== 'begin') {
            $this->setError('begin');
            return false;
        }
        $this->print('begin');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() === 'id' |
            $this->getLexicKey() === 'begin' |
            $this->getLexicKey() === 'all' |
            $this->getLexicKey() === 'while' |
            $this->getLexicKey() === 'repeat' |
            $this->getLexicKey() !== 'if'
        ) {
            $command = $this->command();
            if (!$command) {
                return false;
            }

            while ($this->getLexicKey() !== 'end') {
                $command = $this->command();
                if (!$command) {
                    return false;
                }
            }
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

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        echo htmlspecialchars("</Block>") . '<br>';
        return true;
    }

    private function command()
    {
        echo htmlspecialchars("<Command>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicKey() === 'id' | $this->getLexicKey() === 'begin' | $this->getLexicKey() === 'all') {
            $basicCommand = $this->basicCommand();
            if (!$basicCommand) {
                return false;
            }

            return true;
        }

        if ($this->getLexicKey() === 'while' | $this->getLexicKey() === 'repeat') {
            $iteration = $this->iteration();
            if (!$iteration) {
                return false;
            }
            return true;
        }

        if ($this->getLexicKey() !== 'if') {
            $this->setError('if');
            return false;
        }
        $this->print('if');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== '(') {
            $this->setError('(');
            return false;
        }
        $this->print('(');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $relationalExpression = $this->relationalExpression();
        if (!$relationalExpression) {
            return false;
        }

        if ($this->getLexicKey() !== ')') {
            $this->setError(')');
            return false;
        }
        $this->print(')');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== 'then') {
            $this->setError('then');
            return false;
        }
        $this->print('then');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $command = $this->command();
        if (!$command) {
            return false;
        }

        if ($this->getLexicKey() === 'else') {
            $this->print('else');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $command = $this->command();
            if (!$command) {
                return false;
            }
            return true;
        }

        echo htmlspecialchars("</Command>") . '<br>';
        return true;
    }

    private function basicCommand()
    {
        echo htmlspecialchars("<Basic_Command>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicKey() === 'id') {
            $attribution = $this->attribution();
            if (!$attribution) {
                return false;
            }
            return true;
        }

        if ($this->getLexicKey() === 'begin') {
            $block = $this->block();
            if (!$block) {
                return false;
            }
            return true;
        }

        if ($this->getLexicKey() !== 'all') {
            $this->setError('all');
            return false;
        }
        $this->print('all');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== '(') {
            $this->setError('(');
            return false;
        }
        $this->print('(');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== 'id') {
            $this->setError('id');
            return false;
        }
        $this->print('id');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() === ',') {
            while ($this->getLexicKey() !== ')') {
                if ($this->getLexicKey() !== ',') {
                    $this->setError(',');
                    return false;
                }
                $this->print(',');

                $this->lexicTable->next();
                $this->lexicIndexTable->next();

                if ($this->getLexicKey() !== 'id') {
                    $this->setError('id');
                    return false;
                }
                $this->print('id');

                $this->lexicTable->next();
                $this->lexicIndexTable->next();
            }
        }

        if ($this->getLexicKey() !== ')') {
            $this->setError(')');
            return false;
        }
        $this->print(')');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== ';') {
            $this->setError(';');
            return false;
        }
        $this->print(';');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();


        echo htmlspecialchars("</Basic_Command>") . '<br>';
        return true;
    }

    private function attribution()
    {
        echo htmlspecialchars("<Attribution>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        echo htmlspecialchars("</Attribution>") . '<br>';
        return true;
    }

    private function arithmeticExpression()
    {
        echo htmlspecialchars("<Arithmetic_Expression>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        echo htmlspecialchars("</Arithmetic_Expression>") . '<br>';
        return true;
    }

    private function comment()
    {
        echo htmlspecialchars("<Comment>") . '<br>';

        if ($this->getLexicKey() !== '{') {
            $this->setError('{');
            return false;
        }
        $this->print('{');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== "'") {
            $this->setError("'");
            return false;
        }
        $this->print("'");

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        while ($this->getLexicKey() !== "'") {
            $this->print("'");

            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }

        if ($this->getLexicKey() !== "'") {
            $this->setError("'");
            return false;
        }
        $this->print("'");

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== '}') {
            $this->setError('}');
            return false;
        }
        $this->print('}');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        echo htmlspecialchars("</Comment>") . '<br>';
        return true;
    }

    private function value()
    {
        echo htmlspecialchars("<Value>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicKey() !== "id" && $this->getLexicKey() !== "integer" && $this->getLexicKey() !== "real") {
            $this->setError("id ou integer ou real");
            return false;
        }
        $this->print($this->getLexicKey());

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        echo htmlspecialchars("</Value>") . '<br>';
        return true;
    }

    private function iteration()
    {
        echo htmlspecialchars("<Iteration>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

            $relationalExpression = $this->relationalExpression();
            if (!$relationalExpression) {
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

        if ($this->getLexicKey() !== 'repeat') {
            $this->setError('repeat');
            return false;
        }

        $this->print('repeat');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $command = $this->command();
        if (!$command) {
            return false;
        }

        if ($this->getLexicKey() !== 'until') {
            $this->setError('until');
            return false;
        }

        $this->print('until');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== '(') {
            $this->setError('(');
            return false;
        }

        $this->print('(');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $relationalExpression = $this->relationalExpression();
        if (!$relationalExpression) {
            return false;
        }

        if ($this->getLexicKey() !== ')') {
            $this->setError(')');
            return false;
        }

        $this->print(')');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        if ($this->getLexicKey() !== ';') {
            $this->setError(';');
            return false;
        }

        $this->print(';');

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        echo htmlspecialchars("</Iteration>") . '<br>';
        return true;
    }

    private function relationalExpression()
    {
        echo htmlspecialchars("<Relational_Expression>") . '<br>';

        if ($this->getLexicKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicKey() === '(') {
            $this->print('(');

            $this->lexicTable->next();
            $this->lexicIndexTable->next();

            $relationalExpression = $this->relationalExpression();
            if (!$relationalExpression) {
                return false;
            }

            $this->lexicTable->next();
            $this->lexicIndexTable->next();


            while ($this->getLexicKey() === 'and' | $this->getLexicKey() === 'or') {
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

                $relationalExpression = $this->relationalExpression();
                if (!$relationalExpression) {
                    return false;
                }

                if ($this->getLexicKey() !== ')') {
                    $this->setError(')');
                    return false;
                }

                $this->print(')');

                $this->lexicTable->next();
                $this->lexicIndexTable->next();
            }

            return true;
        }

        $value = $this->value();
        if (!$value) {
            return false;
        }

        if ($this->getLexicKey() !== '<=' &&
            $this->getLexicKey() !== '>=' &&
            $this->getLexicKey() !== '<>' &&
            $this->getLexicKey() !== '=' &&
            $this->getLexicKey() !== '<' &&
            $this->getLexicKey() !== '>') {
            $this->setError('<= ou >= ou <> ou = ou < ou >');
            return false;
        }

        $this->print($this->getLexicKey());

        $this->lexicTable->next();
        $this->lexicIndexTable->next();

        $value = $this->value();
        if (!$value) {
            return false;
        }

        echo htmlspecialchars("</Relational_Expression>") . '<br>';
        return true;
    }
}

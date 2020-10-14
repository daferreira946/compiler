<?php


namespace Compiler\src;

use ArrayIterator;

class Syntax
{
    private string $error;
    private ArrayIterator $lexicalTable;
    private ArrayIterator $lexicalIndexTable;

    public function __construct(ArrayIterator $lexicalTable, ArrayIterator $lexicalIndexTable)
    {
        $this->lexicalTable = $lexicalTable;
        $this->lexicalIndexTable = $lexicalIndexTable;
    }

    public function syntaxAnalyser(): bool
    {
        echo "<h1>Análise sintática</h1>";

        $program = $this->program();
        if (!$program) {
            echo $this->error . "<br>";
            return false;
        }
        return true;
    }

    private function setError(string $expected): void
    {
        $key = $this->lexicalIndexTable->key();
        $this->lexicalIndexTable->seek($key-1);
        $this->error = 'Erro sintático: esperado [' . $expected . '], encontrado [' . $this->getLexicalKey()
        . ']. Logo após o elemento na: ' . $this->lexicalIndexTable->current();
        $this->lexicalIndexTable->next();
        echo $this->lexicalTable->key() . " ";
        echo $this->lexicalIndexTable->key() . "<br>";
    }

    private function getLexicalKey(): string
    {
        //Pegando chave da array atual do iterator
        return key($this->lexicalTable->current());
    }

    private function getLexicalValue(): string
    {
        //Pegando o valor atual do iterator
        return $this->lexicalTable->current()[$this->getLexicalKey()];
    }

    private function print(string $expected): void
    {
        print $expected;
        print " => ";
        print $this->getLexicalKey();
        print " = ";
        print $this->getLexicalValue();
        print "<br>";
    }

    private function program(): bool
    {
        echo htmlspecialchars("<Program>") . '<br>';

        $mainBlock = $this->mainBlock();
        if (!$mainBlock) {
            return false;
        }

        echo htmlspecialchars("</Program>") . '<br>';

        return true;
    }

    private function mainBlock(): bool
    {
        echo htmlspecialchars("<Main_Block>") . '<br>';

        if ($this->getLexicalKey() !== 'program') {
            $this->setError('program');
            return false;
        }
        $this->print('program');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== 'id') {
            $this->setError('id');
            return false;
        }
        $this->print('id');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== ';') {
            $this->setError(';');
            return false;
        }
        $this->print(';');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() === 'integer' |
            $this->getLexicalKey() === 'real' |
            $this->getLexicalKey() === 'string') {
            $variableDeclaration = $this->variableDeclaration();
            if (!$variableDeclaration) {
                return false;
            }
        }

        if ($this->getLexicalKey() !== 'begin') {
            $this->setError('begin');
            return false;
        }
        $this->print('begin');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $block = $this->block();
        if (!$block) {
            return false;
        }

        if ($this->getLexicalKey() !== 'end') {
            $this->setError('end');
            return false;
        }

        $this->print('end');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== '.') {
            $this->setError('.');
            return false;
        }
        $this->print('.');

        if ($this->lexicalTable->valid() !== false) {
            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();
        }

        echo htmlspecialchars("</Main_Block>") . '<br>';
        return true;
    }

    private function variableDeclaration(): bool
    {
        echo htmlspecialchars("<Variable_Declaration>") . '<br>';

        while ($this->getLexicalKey() === 'integer' |
            $this->getLexicalKey() === 'real' |
            $this->getLexicalKey() === 'string') {
            $this->print($this->getLexicalKey());

            while ($this->getLexicalKey() !== ';') {
                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();
                if ($this->getLexicalKey() !== 'id') {
                    $this->setError('id');
                    return false;
                }
                $this->print('id');

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();
                if ($this->getLexicalKey() !== ',' && $this->getLexicalKey() !== ';') {
                    $this->setError(', ou ;');
                    return false;
                }
                $this->print($this->getLexicalKey());
            }

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();
        }

        echo htmlspecialchars("</Variable_Declaration>") . '<br>';
        return true;
    }

    private function block(): bool
    {
        echo htmlspecialchars("<Block>") . '<br>';

        if ($this->getLexicalKey() !== 'begin') {
            $this->setError('begin');
            return false;
        }
        $this->print('begin');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() === 'id' |
            $this->getLexicalKey() === 'begin' |
            $this->getLexicalKey() === 'all' |
            $this->getLexicalKey() === 'while' |
            $this->getLexicalKey() === 'repeat' |
            $this->getLexicalKey() === 'if'
        ) {
            $command = $this->command();
            if (!$command) {
                return false;
            }

            while ($this->getLexicalKey() !== 'end') {
                $command = $this->command();
                if (!$command) {
                    return false;
                }
            }
        }

        if ($this->getLexicalKey() !== 'end') {
            $this->setError('end');
            return false;
        }
        $this->print('end');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== ';') {
            $this->setError(';');
            return false;
        }
        $this->print(';');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Block>") . '<br>';
        return true;
    }

    private function command(): bool
    {
        /** @noinspection HtmlDeprecatedTag */
        echo htmlspecialchars("<Command>") . '<br>';

        if ($this->getLexicalKey() === 'id' | $this->getLexicalKey() === 'begin' | $this->getLexicalKey() === 'all') {
            $basicCommand = $this->basicCommand();
            if (!$basicCommand) {
                return false;
            }
            return true;
        }

        if ($this->getLexicalKey() === 'while' | $this->getLexicalKey() === 'repeat') {
            $iteration = $this->iteration();
            if (!$iteration) {
                return false;
            }
            return true;
        }

        if ($this->getLexicalKey() !== 'if') {
            $this->setError('if');
            return false;
        }
        $this->print('if');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== '(') {
            $this->setError('(');
            return false;
        }
        $this->print('(');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $relationalExpression = $this->relationalExpression();
        if (!$relationalExpression) {
            return false;
        }

        if ($this->getLexicalKey() !== ')') {
            $this->setError(')');
            return false;
        }
        $this->print(')');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== 'then') {
            $this->setError('then');
            return false;
        }
        $this->print('then');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $command = $this->command();
        if (!$command) {
            return false;
        }

        if ($this->getLexicalKey() === 'else') {
            $this->print('else');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $command = $this->command();
            if (!$command) {
                return false;
            }
            return true;
        }

        echo htmlspecialchars("</Command>") . '<br>';
        return true;
    }

    private function basicCommand(): bool
    {
        echo htmlspecialchars("<Basic_Command>") . '<br>';

        if ($this->getLexicalKey() === 'id') {
            $attribution = $this->attribution();
            if (!$attribution) {
                return false;
            }
            return true;
        }

        if ($this->getLexicalKey() === 'begin') {
            $block = $this->block();
            if (!$block) {
                return false;
            }
            return true;
        }

        if ($this->getLexicalKey() !== 'all') {
            $this->setError('all');
            return false;
        }
        $this->print('all');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== '(') {
            $this->setError('(');
            return false;
        }
        $this->print('(');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== 'id') {
            $this->setError('id');
            return false;
        }
        $this->print('id');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() === ',') {
            while ($this->getLexicalKey() !== ')') {
                if ($this->getLexicalKey() !== ',') {
                    $this->setError(',');
                    return false;
                }
                $this->print(',');

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();

                if ($this->getLexicalKey() !== 'id') {
                    $this->setError('id');
                    return false;
                }
                $this->print('id');

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();
            }
        }

        if ($this->getLexicalKey() !== ')') {
            $this->setError(')');
            return false;
        }
        $this->print(')');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== ';') {
            $this->setError(';');
            return false;
        }
        $this->print(';');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Basic_Command>") . '<br>';
        return true;
    }

    private function attribution(): bool
    {
        echo htmlspecialchars("<Attribution>") . '<br>';

        $this->print('id');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== ':=') {
            $this->setError(':=');
            return false;
        }

        $this->print(':=');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $arithmeticExpression = $this->arithmeticExpression();
        if (!$arithmeticExpression) {
            return false;
        }

        if ($this->getLexicalKey() !== ';') {
            $this->setError(';');
            return false;
        }

        $this->print(';');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Attribution>") . '<br>';
        return true;
    }

    private function arithmeticExpression(): bool
    {
        echo htmlspecialchars("<Arithmetic_Expression>") . '<br>';

        if ($this->getLexicalKey() === '(') {
            $this->print('(');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $arithmeticExpression = $this->arithmeticExpression();
            if (!$arithmeticExpression) {
                return false;
            }

            if ($this->getLexicalKey() !== ')') {
                $this->setError(')');
                return false;
            }

            $this->print(')');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            if ($this->getLexicalKey() !== '+' |
                $this->getLexicalKey() !== '-' |
                $this->getLexicalKey() !== '*' |
                $this->getLexicalKey() !== '/') {
                $this->setError('+ ou - ou * ou /');
                return false;
            }

            $this->print($this->getLexicalKey());

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            if ($this->getLexicalKey() !== '(') {
                $this->setError('(');
                return false;
            }

            $this->print('(');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $arithmeticExpression = $this->arithmeticExpression();
            if (!$arithmeticExpression) {
                return false;
            }

            if ($this->getLexicalKey() !== ')') {
                $this->setError(')');
                return false;
            }

            $this->print(')');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            return true;
        }

        $value = $this->value();
        if (!$value) {
            return false;
        }

        if ($this->getLexicalKey() === '+' |
            $this->getLexicalKey() === '-' |
            $this->getLexicalKey() === '*' |
            $this->getLexicalKey() === '/') {
            $this->print($this->getLexicalKey());

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $value = $this->value();
            if (!$value) {
                return false;
            }

            return true;
        }

        echo htmlspecialchars("</Arithmetic_Expression>") . '<br>';
        return true;
    }

    private function value(): bool
    {
        echo htmlspecialchars("<Value>") . '<br>';

        if ($this->getLexicalKey() !== "id" &&
            $this->getLexicalKey() !== "integer" &&
            $this->getLexicalKey() !== "real") {
            $this->setError("id ou integer ou real");
            return false;
        }
        $this->print($this->getLexicalKey());

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Value>") . '<br>';
        return true;
    }

    private function iteration(): bool
    {
        echo htmlspecialchars("<Iteration>") . '<br>';

        if ($this->getLexicalKey() === 'while') {
            $this->print('while');
            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            if ($this->getLexicalKey() !== '(') {
                $this->setError('(');
                return false;
            }
            $this->print('(');
            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $relationalExpression = $this->relationalExpression();
            if (!$relationalExpression) {
                return false;
            }

            if ($this->getLexicalKey() !== ')') {
                $this->setError(')');
                return false;
            }
            $this->print(')');
            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            if ($this->getLexicalKey() !== 'do') {
                $this->setError('do');
                return false;
            }
            $this->print('do');
            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            return true;
        }

        if ($this->getLexicalKey() !== 'repeat') {
            $this->setError('while ou repeat');
            return false;
        }

        $this->print('repeat');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $command = $this->command();
        if (!$command) {
            return false;
        }

        if ($this->getLexicalKey() !== 'until') {
            $this->setError('until');
            return false;
        }

        $this->print('until');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== '(') {
            $this->setError('(');
            return false;
        }

        $this->print('(');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $relationalExpression = $this->relationalExpression();
        if (!$relationalExpression) {
            return false;
        }

        if ($this->getLexicalKey() !== ')') {
            $this->setError(')');
            return false;
        }

        $this->print(')');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== ';') {
            $this->setError(';');
            return false;
        }

        $this->print(';');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Iteration>") . '<br>';
        return true;
    }

    private function relationalExpression(): bool
    {
        echo htmlspecialchars("<Relational_Expression>") . '<br>';

        if ($this->getLexicalKey() === '(') {
            $this->print('(');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $relationalExpression = $this->relationalExpression();
            if (!$relationalExpression) {
                return false;
            }

            if ($this->getLexicalKey() !== ')') {
                $this->setError(')');
                return false;
            }

            $this->print(')');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            while ($this->getLexicalKey() === 'and' | $this->getLexicalKey() === 'or') {
                $this->print($this->getLexicalKey());

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();

                if ($this->getLexicalKey() !== '(') {
                    $this->setError('(');
                    return false;
                }

                $this->print('(');

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();

                $relationalExpression = $this->relationalExpression();
                if (!$relationalExpression) {
                    return false;
                }

                if ($this->getLexicalKey() !== ')') {
                    $this->setError(')');
                    return false;
                }

                $this->print(')');

                $this->lexicalTable->next();
                $this->lexicalIndexTable->next();
            }

            return true;
        }

        $value = $this->value();
        if (!$value) {
            return false;
        }

        if ($this->getLexicalKey() !== '<=' &&
            $this->getLexicalKey() !== '>=' &&
            $this->getLexicalKey() !== '<>' &&
            $this->getLexicalKey() !== '=' &&
            $this->getLexicalKey() !== '<' &&
            $this->getLexicalKey() !== '>') {
            $this->setError('<= ou >= ou <> ou = ou < ou >');
            return false;
        }

        $this->print($this->getLexicalKey());

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        $value = $this->value();
        if (!$value) {
            return false;
        }

        echo htmlspecialchars("</Relational_Expression>") . '<br>';
        return true;
    }
}

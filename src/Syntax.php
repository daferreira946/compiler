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
        if (!$this->program()) {
            echo "<pre>";
            var_dump($this->getError());
            echo "</pre>";
            return $this->getError();
        }

        return true;
    }

    private function getError(): string
    {
        return $this->error;
    }

    private function setError(string $expected): void
    {
        $this->error = 'Erro sintático: esperado ' . $expected . ', encontrado ' . $this->getLexicalKey()
        . '. ' . $this->lexicalIndexTable->current();
    }

    private function getLexicalKey(): string
    {
        return key($this->lexicalTable->current());
    }

    private function getLexicalValue(): string
    {
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

        if ($this->getLexicalKey() === '{') {
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

    private function mainBlock(): bool
    {
        echo htmlspecialchars("<Main_Block>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        $variableDeclaration = $this->variableDeclaration();
        if (!$variableDeclaration) {
            return false;
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

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->lexicalTable->valid() !== false) {
            if ($this->getLexicalKey() !== '{') {
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

    private function variableDeclaration(): bool
    {
        echo htmlspecialchars("<Variable_Declaration>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Variable_Declaration>") . '<br>';
        return true;
    }

    private function block(): bool
    {
        echo htmlspecialchars("<Block>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
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

        if ($this->getLexicalKey() === 'id' |
            $this->getLexicalKey() === 'begin' |
            $this->getLexicalKey() === 'all' |
            $this->getLexicalKey() === 'while' |
            $this->getLexicalKey() === 'repeat' |
            $this->getLexicalKey() !== 'if'
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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Block>") . '<br>';
        return true;
    }

    private function command(): bool
    {
        /** @noinspection HtmlDeprecatedTag */
        echo htmlspecialchars("<Command>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Command>") . '<br>';
        return true;
    }

    private function basicCommand(): bool
    {
        echo htmlspecialchars("<Basic_Command>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Basic_Command>") . '<br>';
        return true;
    }

    private function attribution(): bool
    {
        echo htmlspecialchars("<Attribution>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Attribution>") . '<br>';
        return true;
    }

    private function arithmeticExpression(): bool
    {
        echo htmlspecialchars("<Arithmetic_Expression>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Arithmetic_Expression>") . '<br>';
        return true;
    }

    private function comment(): bool
    {
        echo htmlspecialchars("<Comment>") . '<br>';

        if ($this->getLexicalKey() !== '{') {
            $this->setError('{');
            return false;
        }
        $this->print('{');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== "'") {
            $this->setError("'");
            return false;
        }
        $this->print("'");

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        while ($this->getLexicalKey() !== "'") {
            $this->print("'");

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();
        }

        if ($this->getLexicalKey() !== "'") {
            $this->setError("'");
            return false;
        }
        $this->print("'");

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() !== '}') {
            $this->setError('}');
            return false;
        }
        $this->print('}');

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        echo htmlspecialchars("</Comment>") . '<br>';
        return true;
    }

    private function value(): bool
    {
        echo htmlspecialchars("<Value>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicalKey() !== "id" &&
            $this->getLexicalKey() !== "integer" &&
            $this->getLexicalKey() !== "real") {
            $this->setError("id ou integer ou real");
            return false;
        }
        $this->print($this->getLexicalKey());

        $this->lexicalTable->next();
        $this->lexicalIndexTable->next();

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Value>") . '<br>';
        return true;
    }

    private function iteration(): bool
    {
        echo htmlspecialchars("<Iteration>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

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
            $this->setError('repeat');
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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Iteration>") . '<br>';
        return true;
    }

    private function relationalExpression(): bool
    {
        echo htmlspecialchars("<Relational_Expression>") . '<br>';

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        if ($this->getLexicalKey() === '(') {
            $this->print('(');

            $this->lexicalTable->next();
            $this->lexicalIndexTable->next();

            $relationalExpression = $this->relationalExpression();
            if (!$relationalExpression) {
                return false;
            }

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

        if ($this->getLexicalKey() === '{') {
            $comment = $this->comment();
            if (!$comment) {
                return false;
            }
        }

        echo htmlspecialchars("</Relational_Expression>") . '<br>';
        return true;
    }
}

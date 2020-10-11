<?php


namespace Compiler\src;

use ArrayIterator;
use ArrayObject;

class Syntax
{
    private array $gramatic;
    private ArrayIterator $lexicTable;
    private ArrayIterator $lexicIndexTable;
    private $error;

    public function __construct(ArrayIterator $lexicTable, ArrayIterator $lexicIndexTable)
    {
        $this->lexicTable = $lexicTable;
        $this->lexicIndexTable = $lexicIndexTable;
        $json = file_get_contents('../gramatic.json');
        $this->gramatic = json_decode($json, true);
        $this->lexicTable = $lexicTable;
    }

    public function syntaxAnalyser()
    {
        if ($this->program() === false) {
            echo "<pre>";
            var_dump($this->getError());
            echo "</pre>";
            return $this->getError();
        }

        echo "<pre>";
        var_dump($this->program());
        echo "</pre>";
    }

    private function getError()
    {
        return 'Erro sintático: esperado ' . $this->error['expected'] . ', encontrado ' . $this->error['founded']
            . '. ' . $this->lexicIndexTable->current();
    }

    /**
     * @return false|mixed
     */
    private function program()
    {
        if (!array_key_exists('program', $this->gramatic)) {
            return false;
        }
        $main_block = $this->mainBlock();
        if ($main_block === false) {
            return false;
        }

        return $main_block;
    }

    /**
     * @return false|mixed
     */
    private function mainBlock()
    {
        if (!array_key_exists('main_block', $this->gramatic)) {
            return false;
        }
        $mainBlock = new ArrayObject($this->gramatic['main_block']);
        $mainBlockIterator = $mainBlock->getIterator();

        while ($mainBlockIterator->valid() && $this->lexicTable->valid()) {
            if ($mainBlockIterator->current() === '[') {
                $mainBlockIterator->next();
                if ($mainBlockIterator->current() !== 'variable_declaration') {
                    print "Problema nas configurações";
                    die();
                }
                $mainBlockIterator->next();
                if ($mainBlockIterator->current() !== ']') {
                    print "Problema nas configurações";
                    die();
                }
                $mainBlockIterator->next();
                $this->variableDeclaration($mainBlockIterator->current());
                $mainBlockIterator->next();
            }
            if ($mainBlockIterator->current() === 'block') {
                $this->block();
                $mainBlockIterator->next();
            }
            foreach ($this->lexicTable->current() as $key => $value) {
                if ($mainBlockIterator->current() !== $key) {
                    $this->error['expected'] = $mainBlockIterator->current();
                    $this->error['founded'] = $mainBlockIterator->current();
                    return false;
                }

                print $mainBlockIterator->current();
                print " => ";
                print $key;
                print " = ";
                print $value;
                print "<br>";
            }

            $mainBlockIterator->next();
            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }

        return;
    }

    private function variableDeclaration(string $repeat)
    {
        
    }

    private function block()
    {

    }
}

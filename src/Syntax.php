<?php


namespace Compiler\src;

use ArrayIterator;

class Syntax
{
    private array $gramatic;
    private ArrayIterator $lexicTable;
    private ArrayIterator $lexicIndexTable;
    private array $error;

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
        if (!$this->program()) {
            echo "<pre>";
            var_dump($this->getError());
            echo "</pre>";
            return $this->getError();
        }

        echo "<pre>";
        var_dump($this->program());
        echo "</pre>";

        return true;
    }

    private function getError()
    {
        return 'Erro sintático: esperado ' . $this->error['expected'] . ', encontrado ' . $this->error['founded']
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

    /**
     * @return false|mixed
     */
    private function program()
    {
        $main_block = $this->mainBlock();
        if (!$main_block) {
            return false;
        }

        return true;
    }

    /**
     * @return false|mixed
     */
    private function mainBlock()
    {
        $mainBlockIterator = new ArrayIterator($this->gramatic['main_block']);

        while ($mainBlockIterator->valid() && $this->lexicTable->valid()) {
            if ($mainBlockIterator->current() === '[') {
                $mainBlockIterator->next();
                $variableDeclaration = $this->variableDeclaration();
                if ($variableDeclaration === false) {
                    return false;
                }
                $mainBlockIterator->next();
                $mainBlockIterator->next();
                $mainBlockIterator->next();
            }
            if ($mainBlockIterator->current() === 'block') {
                $this->block();
                $mainBlockIterator->next();
            }
            if ($mainBlockIterator->current() !== $this->getLexicKey()) {
                $this->error['expected'] = $mainBlockIterator->current();
                $this->error['founded'] = $this->getLexicKey();
                return false;
            }

            print $mainBlockIterator->current();
            print " => ";
            print $this->getLexicKey();
            print " = ";
            print $this->getLexicValue();
            print "<br>";

            $mainBlockIterator->next();
            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }

        return true;
    }

    private function variableDeclaration()
    {
        $variableDeclarationIterator = new ArrayIterator($this->gramatic['variable_declaration']);

        while ($variableDeclarationIterator->valid()) {
            if ($variableDeclarationIterator->current() === 'type') {
                $type = $this->type();
                if (!$type) {
                    return false;
                }
                $variableDeclarationIterator->next();
            }
            if ($variableDeclarationIterator->current() === '[') {
                while ($variableDeclarationIterator->current() !== '*') {
                    $variableDeclarationIterator->next();
                    if ($variableDeclarationIterator->current() !== $this->getLexicKey()) {
                        $this->error['expected'] = $variableDeclarationIterator->current();
                        $this->error['founded'] = $this->getLexicKey();
                        return false;
                    }
                    print $variableDeclarationIterator->current();
                    print " => ";
                    print $this->getLexicKey();
                    print " = ";
                    print $this->getLexicValue();
                    print "<br>";
                    $this->lexicTable->next();
                    $this->lexicIndexTable->next();
                }
                $variableDeclarationIterator->next();
            }
            if ($variableDeclarationIterator->current() !== $this->getLexicKey()) {
                $this->error['expected'] = $variableDeclarationIterator->current();
                $this->error['founded'] = $this->getLexicKey();
                return false;
            }

            print $variableDeclarationIterator->current();
            print " => ";
            print $this->getLexicKey();
            print " = ";
            print $this->getLexicValue();
            print "<br>";

            $variableDeclarationIterator->next();
            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }

        return true;
    }

    private function block()
    {
    }
<<<<<<< HEAD

    private function type()
    {
        $typeIterator = new ArrayIterator($this->gramatic['type']);

        while ($typeIterator->valid()) {
            if ($typeIterator->current() === $this->getLexicValue()) {
                print $typeIterator->current();
                print " => ";
                print $this->getLexicKey();
                print " = ";
                print $this->getLexicValue();
                print "<br>";

                $typeIterator->next();
=======
     /*"type" : [
        "integer",
        "|",
        "real",
        "|",
        "string"
    ]
    */
    private function type()
    {       
        $type = new ArrayObject($this->gramatic['type']);
        $typeIterator = $type->getIterator();     
        while($typeIterator->valid()){
            if($typeIterator->current() === $this->lexicTable->current()){
>>>>>>> 0d1a253... Funcao comment e value
                $this->lexicTable->next();
                $this->lexicIndexTable->next();
                return true;
            }
            $typeIterator->next();
<<<<<<< HEAD
        }

        $this->error['expected'] = $typeIterator->current();
        $this->error['founded'] = $this->getLexicKey();
=======
        }        
>>>>>>> 0d1a253... Funcao comment e value
        return false;
    }
    /*
    "comment" : [
        "{",
        "'",
        "?",
        "'",
        "}"
      ]
     */
    private function comment()
    {     
        if($this->lexicTable->current() !== "{"){            
            return false;
        }    
        while($this->lexicTable->valid()){
            if($this->lexicTable->current() === "}"){ 
                $this->lexicTable->next();
                $this->lexicIndexTable->next();                
                return true;
            }
            $this->lexicTable->next();
            $this->lexicIndexTable->next();
        }        
        return false;
    } 
    /* "value" : [
        "id",
        "|",
        "integer",
        "|",
        "real"
    ]*/
    private function value()
    {     
        $value = new ArrayObject($this->gramatic['value']);
        $valueIterator = $value->getIterator(); 
        while($valueIterator->valid()){
            if($valueIterator->current() === $this->lexicTable->current()){
                $this->lexicTable->next();
                $this->lexicIndexTable->next();
                return true;
            }
            $valueIterator->next();
        }        
        return false;

    }    

}

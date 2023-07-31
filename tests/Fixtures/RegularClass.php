<?php

namespace Henzeb\VarExportWrapper\Tests\Fixtures;

class RegularClass
{
    private $myVariable;

    public function setVariable($variable) {
        $this->myVariable = $variable;
    }

    public function getVariable()
    {
        return $this->myVariable;
    }
}

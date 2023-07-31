<?php

namespace Henzeb\VarExportWrapper\Tests\Fixtures;

class ExportableClass
{
    private $myVariable;

    public function setVariable($variable) {
        $this->myVariable = $variable;
    }

    public function getVariable()
    {
        return $this->myVariable;
    }

    public static function __set_state($array)
    {
        $obj = new ExportableClass();

        $obj->myVariable = 'hello ' . $array['myVariable'];

        return $obj;
    }
}

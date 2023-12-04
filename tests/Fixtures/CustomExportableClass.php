<?php

namespace Henzeb\VarExportWrapper\Tests\Fixtures;

class CustomExportableClass
{
    private $myVariable;

    public function setVariable($variable) {
        $this->myVariable = $variable;
    }

    public function getVariable()
    {
        return $this->myVariable;
    }

    public static function __set_state($array): self
    {
        $obj = new CustomExportableClass();

        $obj->myVariable = $array['myVariable'];

        return $obj;
    }

    public function __get_state(): array
    {
        return [
            'myVariable' => $this->myVariable
        ];
    }
}

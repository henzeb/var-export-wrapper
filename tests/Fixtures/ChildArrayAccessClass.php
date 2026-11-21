<?php

namespace Henzeb\VarExportWrapper\Tests\Fixtures;

class ChildArrayAccessClass extends ArrayAccessClass
{
    private $own;

    public function __construct()
    {
        parent::__construct();
        $this->own = static fn () => 'own';
    }

    public function getOwn()
    {
        return $this->own;
    }
}

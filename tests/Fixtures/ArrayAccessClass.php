<?php

namespace Henzeb\VarExportWrapper\Tests\Fixtures;

use ArrayAccess;

class ArrayAccessClass implements ArrayAccess
{
    public $public;
    protected $protected;
    private $private;
    public array $nested;

    public function __construct()
    {
        $this->public = static fn () => 'public';
        $this->protected = static fn () => 'protected';
        $this->private = static fn () => 'private';
        $this->nested = ['closure' => static fn () => 'nested', 'scalar' => 'scalar'];
    }

    public function get(string $property)
    {
        return $this->$property;
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public static function __set_state(array $state)
    {
        return new static();
    }
}

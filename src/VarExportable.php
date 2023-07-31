<?php

namespace Henzeb\VarExportWrapper;

use Closure;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\UnsignedSerializableClosure;
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;

class VarExportable
{
    private $object;

    /**
     * @throws PhpVersionNotSupportedException
     */
    public function __construct(
        object $var
    )
    {
        if ($var instanceof Closure) {
            $var = new SerializableClosure($var);
        }

        /**
         * If the object implements __set_state, it does not need to serialize
         */
        if (is_exportable($var)) {
            $this->object = $var;
            return;
        }

        $this->object = serialize(
            $var
        );
    }

    public static function __set_state(array $state)
    {
        return self::setState($state['object']);
    }

    private static function setState($export): object
    {
        if (is_string($export)) {
            $export = unserialize($export);
        }

        if ($export instanceof SerializableClosure
            || $export instanceof UnsignedSerializableClosure
        ) {
            $export = $export->getClosure();
        }

        return $export;
    }

    /**
     * @return Closure|mixed|object
     */
    public function getObject()
    {
        return self::setState($this->object);
    }
}

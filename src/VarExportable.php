<?php

namespace Henzeb\VarExportWrapper;

use Closure;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\UnsignedSerializableClosure;
use function Henzeb\VarExportWrapper\Support\Functions\exportify;
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;
use function Henzeb\VarExportWrapper\Support\Functions\var_import;

class VarExportable
{
    private $object;
    private ?string $class = null;

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
         * If the object implements __set_state, it does not need to serialize,
         * unless it has __get_state implemented.
         */
        if (is_exportable($var)) {
            if (!method_exists($var, '__get_state')) {
                $this->object = $var;
                return;
            }

            $this->class = get_class($var);
            $this->object = exportify($var->__get_state());
            return;
        }

        $this->object = \serialize(
            $var
        );
    }

    public static function __set_state(array $state)
    {
        return self::setState($state['object'], $state['class'] ?? null);
    }

    /**
     * @param object|string $export
     * @param string|null $class
     * @return object|Closure|mixed
     * @throws PhpVersionNotSupportedException
     */
    private static function setState($export, ?string $class): object
    {
        if (is_string($export)) {
            $export = unserialize($export);
        }

        if($class) {
            /** @var object $class */
            return $class::__set_state(
                var_import($export)
            );
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
        return self::setState($this->object, $this->class);
    }
}

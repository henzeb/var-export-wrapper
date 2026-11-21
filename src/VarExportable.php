<?php

namespace Henzeb\VarExportWrapper;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\UnsignedSerializableClosure;
use UnexpectedValueException;

use function Henzeb\VarExportWrapper\Support\Functions\exportify;
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;
use function Henzeb\VarExportWrapper\Support\Functions\var_import;

class VarExportable
{
    private mixed $object;

    /**
     * @var class-string|null
     */
    private ?string $class = null;

    public function __construct(
        object $var
    ) {
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

    /**
     * @param array{object: mixed, class?: class-string|null} $state
     * @return object
     */
    public static function __set_state(array $state)
    {
        return self::setState($state['object'], $state['class'] ?? null);
    }

    /**
     * @param class-string|null $class
     */
    private static function setState(mixed $export, ?string $class): object
    {
        if (is_string($export)) {
            $export = unserialize($export);
        }

        if ($class !== null && method_exists($class, '__set_state')) {
            $export = $class::__set_state(var_import($export));
        } elseif ($export instanceof SerializableClosure
            || $export instanceof UnsignedSerializableClosure
        ) {
            $export = $export->getClosure();
        }

        if (!is_object($export)) {
            throw new UnexpectedValueException('The exported value could not be restored to an object.');
        }

        return $export;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return self::setState($this->object, $this->class);
    }
}

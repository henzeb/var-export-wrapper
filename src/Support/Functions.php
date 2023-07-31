<?php

namespace Henzeb\VarExportWrapper\Support\Functions;


use ArrayAccess;
use Henzeb\VarExportWrapper\VarExportable;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Traversable;

/**
 * @var $var mixed
 * @throws PhpVersionNotSupportedException
 * @return VarExportable|object|array|mixed
 */
function exportify($var)
{
    if (\is_array($var) || $var instanceof ArrayAccess || $var instanceof Traversable) {

        \array_walk_recursive(
            $var,
            function (&$var) {
                if (!is_exportable($var)) {
                    $var = exportify($var);
                }
            }
        );
    }

    if(is_exportable($var)) {
        return $var;
    }

    return new VarExportable($var);
}

/**
 * @param $var mixed
 * @return bool
 */
function is_exportable($var): bool
{
    if (is_array($var)) {
        foreach ($var as $item) {
            if (!is_exportable($item)) {
                return false;
            }
        }
        return true;
    }

    return !is_object($var) || method_exists($var, '__set_state');
}

/**
 * @param $var mixed
 * @param bool $return
 * @return string|null
 * @throws PhpVersionNotSupportedException
 */
function var_export($var, bool $return = false): ?string
{
    return \var_export(
        exportify($var),
        $return
    );
}

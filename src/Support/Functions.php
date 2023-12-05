<?php

namespace Henzeb\VarExportWrapper\Support\Functions;


use ArrayAccess;
use Henzeb\VarExportWrapper\VarExportable;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Traversable;

/**
 * @return VarExportable|object|array|mixed
 * @throws PhpVersionNotSupportedException
 * @var $var mixed
 */
function exportify($var)
{
    if (\is_array($var) || $var instanceof ArrayAccess || $var instanceof Traversable) {

        \array_walk_recursive(
            $var,
            function (&$var) {
                if (!is_exportable($var)
                    || (is_object($var)
                        && method_exists($var, '__get_state')
                    )
                ) {
                    $var = exportify($var);
                }
            }
        );
    }

    if (is_exportable($var)
        && (!is_object($var)
            || !method_exists($var, '__get_state')
        )
    ) {
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

/**
 * @param string $path
 * @param $var
 * @return false|int
 * @throws PhpVersionNotSupportedException
 */
function var_export_file(string $path, $var)
{
    return \file_put_contents(
        $path,
        '<?php return ' . var_export($var, true) . ';' . PHP_EOL
    );
}

/**
 * Allows for easy import of exported variables. If the variable is an array it will evaluate any
 * VarExportable objects in the array.
 *
 * @param $var mixed
 * @return mixed
 */
function var_import($var)
{
    if (\is_string($var)) {
        $var = \is_file($var) ? require $var : eval('return ' . $var . ';');
    }

    if (\is_array($var)) {
        foreach ($var as &$item)
            if ($item instanceof VarExportable) {
                $item = $item->getObject();
            }
    }

    if ($var instanceof VarExportable) {
        return $var->getObject();
    }

    return $var;
}

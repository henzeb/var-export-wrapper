<?php

namespace Henzeb\VarExportWrapper\Support\Functions;


use ArrayAccess;
use Henzeb\VarExportWrapper\VarExportable;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use ReflectionProperty;
use Traversable;

/**
 * @return VarExportable|object|array|mixed
 * @throws PhpVersionNotSupportedException
 * @var $var mixed
 */
function exportify($var)
{
    $walker = function (&$item) {
        if (!is_exportable($item)
            || (is_object($item)
                && method_exists($item, '__get_state')
            )
        ) {
            $item = exportify($item);
        }
    };

    if (\is_array($var)) {
        \array_walk_recursive($var, $walker);
    } elseif ($var instanceof ArrayAccess || $var instanceof Traversable) {
        foreach (\get_mangled_object_vars($var) as $key => $value) {
            $exported = $value;

            if (\is_array($exported)) {
                \array_walk_recursive($exported, $walker);
            } else {
                $walker($exported);
            }

            if ($exported === $value) {
                continue;
            }

            $parts = \explode("\0", (string)$key);
            $declared = \count($parts) === 3 && $parts[1] !== '*';

            $property = new ReflectionProperty(
                $declared ? $parts[1] : $var,
                \count($parts) === 3 ? $parts[2] : (string)$key
            );

            if (\method_exists($property, 'isReadOnly') && $property->isReadOnly()) {
                continue;
            }

            if (\PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }

            $property->setValue($var, $exported);
        }
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

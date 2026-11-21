<?php

namespace Henzeb\VarExportWrapper\Support\Functions;

use ArrayAccess;
use Henzeb\VarExportWrapper\VarExportable;
use ReflectionProperty;
use Traversable;

function exportify(mixed $var): mixed
{
    $walker = function (mixed &$item): void {
        if (!is_exportable($item)
            || (
                is_object($item)
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
                $declared && \class_exists($parts[1]) ? $parts[1] : $var,
                \count($parts) === 3 ? $parts[2] : (string)$key
            );

            if ($property->isReadOnly()) {
                continue;
            }

            $property->setValue($var, $exported);
        }
    }

    if (is_object($var)
        && (
            !is_exportable($var)
            || method_exists($var, '__get_state')
        )
    ) {
        return new VarExportable($var);
    }

    return $var;
}

function is_exportable(mixed $var): bool
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

function var_export(mixed $var, bool $return = false): ?string
{
    return \var_export(
        exportify($var),
        $return
    );
}

function var_export_file(string $path, mixed $var): int|false
{
    return \file_put_contents(
        $path,
        '<?php return ' . var_export($var, true) . ';' . PHP_EOL
    );
}

/**
 * Allows for easy import of exported variables. If the variable is an array it will evaluate any
 * VarExportable objects in the array.
 */
function var_import(mixed $var): mixed
{
    if (\is_string($var)) {
        $var = \is_file($var) ? require $var : eval('return ' . $var . ';');
    }

    if (\is_array($var)) {
        foreach ($var as &$item) {
            if ($item instanceof VarExportable) {
                $item = $item->getObject();
            }
        }
    }

    if ($var instanceof VarExportable) {
        return $var->getObject();
    }

    return $var;
}

<?php

namespace Henzeb\VarExportWrapper\Tests\Unit;

use Closure;
use Henzeb\VarExportWrapper\Tests\Fixtures\CustomExportableClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\ExportableClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\RegularClass;
use Henzeb\VarExportWrapper\VarExportable;
use Laravel\SerializableClosure\SerializableClosure;
use Laravel\SerializableClosure\UnsignedSerializableClosure;
use PHPUnit\Framework\TestCase;
use function Henzeb\VarExportWrapper\Support\Functions\var_export as varExport;
use function Henzeb\VarExportWrapper\Support\Functions\var_import;

class VarExportableTest extends TestCase
{
    public function testExportsClosure(): void
    {
        $exportable = new VarExportable(
            function () {
                return 'test';
            }
        );

        $closure = $exportable->getObject();

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());

        $export = 'return ' . var_export(
               $exportable,
                true
            ) . ';';

        $closure = eval($export);

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());
    }

    public function testExportsSerializableClosure(): void
    {
        $exportable = new VarExportable(
            new SerializableClosure(
                function () {
                    return 'test';
                }
            )
        );

        $closure = $exportable->getObject();

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());

        $export = 'return ' . var_export(
                $exportable,
                true
            ) . ';';

        $closure = eval($export);

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());
    }

    public function testExportsUnsignedSerializableClosure(): void
    {
        $exportable = new VarExportable(
        new UnsignedSerializableClosure(
            function () {
                return 'test';
            }
        )
    );

        $closure = $exportable->getObject();

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());

        $export = 'return ' . var_export(
                $exportable,
                true
            ) . ';';
        $closure = eval($export);

        $this->assertInstanceOf(Closure::class, $closure);

        $this->assertEquals('test', $closure());
    }

    public function testExportsExportableClass() {
        $exportable = new ExportableClass();
        $exportable->setVariable('world');
        $exportable = new VarExportable(
            $exportable
        );

        $this->assertEquals('world', $exportable->getObject()->getVariable());

        $export = 'return ' . var_export(
                $exportable,
                true
            ) . ';';
        $exportable = eval($export);

        $this->assertEquals('hello world', $exportable->getVariable());
    }

    public function testExportsRegularClass() {
        $exportable = new RegularClass();
        $exportable->setVariable('world');
        $exportable = new VarExportable(
            $exportable
        );

        $this->assertEquals('world', $exportable->getObject()->getVariable());

        $export = 'return ' . var_export(
                $exportable,
                true
            ) . ';';
        $exportable = eval($export);

        $this->assertEquals('world', $exportable->getVariable());
    }

    public function testExportsWithGetState() {
        $exportable = new CustomExportableClass();

        $exportable->setVariable(fn() => 'hello world');

        $exportable = var_import(
            varExport($exportable, true)
        );

        $this->assertEquals('hello world', $exportable->getVariable()());
    }
}

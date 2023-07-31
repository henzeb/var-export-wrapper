<?php

namespace Henzeb\VarExportWrapper\Tests\Unit\Support;

use Henzeb\VarExportWrapper\Tests\Fixtures\ExportableClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\RegularClass;
use Henzeb\VarExportWrapper\VarExportable;
use PHPUnit\Framework\TestCase;
use function Henzeb\VarExportWrapper\Support\Functions\exportify;
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;
use function Henzeb\VarExportWrapper\Support\Functions\var_export as varExport;

class FunctionsTest extends TestCase
{
    public function testExportWithClosure()
    {
        $exportable = exportify(
            function () {
                return 'hello world';
            }
        );
        $this->assertInstanceOf(VarExportable::class, $exportable);

        $this->assertEquals(
            'hello world',
            $exportable->getObject()()
        );
    }

    public function testExportWithRegularClass()
    {
        $object = new RegularClass();
        $object->setVariable('hello world');
        $exportable = exportify(
            $object
        );
        $this->assertInstanceOf(VarExportable::class, $exportable);
        $this->assertInstanceOf(RegularClass::class, $object);

        $this->assertEquals(
            'hello world',
            $exportable->getObject()->getVariable()
        );
    }

    public function testExportWithExportableClass()
    {
        $object = new ExportableClass();
        $object->setVariable('hello world');
        $exportable = exportify(
            $object
        );
        $this->assertInstanceOf(ExportableClass::class, $exportable);
        $this->assertInstanceOf(ExportableClass::class, $object);

        $this->assertEquals(
            'hello world',
            $exportable->getVariable()
        );
    }

    public function testIsExportable()
    {
        $this->assertTrue(is_exportable('string'));
        $this->assertTrue(is_exportable(true));
        $this->assertTrue(is_exportable(1));
        $this->assertTrue(is_exportable(STDIN));
        $this->assertTrue(is_exportable(new ExportableClass()));

        $this->assertFalse(is_exportable(new RegularClass()));
        $this->assertFalse(is_exportable(fn() => true));
    }

    public function testIsArrayExportable()
    {
        $this->assertTrue(is_exportable(['string']));
        $this->assertTrue(is_exportable([true]));
        $this->assertTrue(is_exportable([1]));
        $this->assertTrue(is_exportable([STDIN]));
        $this->assertTrue(is_exportable([new ExportableClass()]));

        $this->assertFalse(is_exportable([new ExportableClass(), new RegularClass()]));
        $this->assertFalse(is_exportable([new ExportableClass(), new RegularClass()]));
        $this->assertFalse(is_exportable([new RegularClass()]));
        $this->assertFalse(is_exportable([fn() => true]));
    }

    public function testVarExport()
    {
        $fn = fn() => true;
        $this->assertEquals(var_export(exportify($fn), true), varExport($fn, true));
        $this->assertEquals(var_export([exportify($fn)], true), varExport([$fn], true));
        $this->assertEquals(var_export([['fn' => exportify($fn)]], true), varExport([['fn' => $fn]], true));
    }
}

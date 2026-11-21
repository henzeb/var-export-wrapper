<?php

namespace Henzeb\VarExportWrapper\Tests\Unit\Support;

use Closure;
use Henzeb\VarExportWrapper\Tests\Fixtures\ArrayAccessClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\ChildArrayAccessClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\ExportableClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\RegularClass;
use Henzeb\VarExportWrapper\VarExportable;
use PHPUnit\Framework\TestCase;

use function Henzeb\VarExportWrapper\Support\Functions\exportify;
use function Henzeb\VarExportWrapper\Support\Functions\is_exportable;
use function Henzeb\VarExportWrapper\Support\Functions\var_export as varExport;
use function Henzeb\VarExportWrapper\Support\Functions\var_export_file;
use function Henzeb\VarExportWrapper\Support\Functions\var_import;

class FunctionsTest extends TestCase
{
    public function testExportifyWrapsPropertiesOfArrayAccessObjects()
    {
        $object = new ArrayAccessClass();

        $this->assertSame($object, exportify($object));

        foreach (['public', 'protected', 'private'] as $property) {
            $this->assertInstanceOf(VarExportable::class, $object->get($property));
            $this->assertSame($property, $object->get($property)->getObject()());
        }

        $this->assertInstanceOf(VarExportable::class, $object->get('nested')['closure']);
        $this->assertSame('nested', $object->get('nested')['closure']->getObject()());
        $this->assertSame('scalar', $object->get('nested')['scalar']);
    }

    public function testExportifyWrapsInheritedPrivatePropertiesOfArrayAccessObjects()
    {
        $object = new ChildArrayAccessClass();

        exportify($object);

        $this->assertInstanceOf(VarExportable::class, $object->get('private'));
        $this->assertInstanceOf(VarExportable::class, $object->getOwn());
        $this->assertSame('own', $object->getOwn()->getObject()());
    }

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
        $this->assertFalse(is_exportable(fn () => true));
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
        $this->assertFalse(is_exportable([fn () => true]));
    }

    public function testVarExport()
    {
        $fn = fn () => true;
        $this->assertEquals(var_export(exportify($fn), true), varExport($fn, true));
        $this->assertEquals(var_export([exportify($fn)], true), varExport([$fn], true));
        $this->assertEquals(var_export([['fn' => exportify($fn)]], true), varExport([['fn' => $fn]], true));
    }

    public function testVarImportWithClosure()
    {
        $fn = fn () => 'hello';

        $this->assertSameResults($fn, var_import(new VarExportable($fn)));
        $this->assertEquals($fn(), var_import(new VarExportable($fn))());
    }

    public function testVarImportWithClosureInArray()
    {
        $array = [fn () => 'hello', 'world'];

        $this->assertSameResults($array, var_import(exportify($array)));
    }

    public function testVarImportWithExportString()
    {
        $array = [fn () => 'hello', 'world'];

        $this->assertSameResults($array, var_import(varExport($array, true)));
    }

    public function testVarImportWithExportFile()
    {
        $array = [fn () => 'hello', 'world'];
        $file = './tests/Fixtures/config.php';

        $this->assertSameResults($array, var_import($file));
    }

    public function testVarExportFile()
    {
        $filename = './tests/Fixtures/tmp.config';
        $this->tearDown();

        $array = [fn () => 'hello', 'world'];

        $result = var_export_file($filename, $array);
        $this->assertSame(filesize($filename), $result);

        $this->assertSameResults($array, var_import($filename));

    }

    private function assertSameResults(mixed $expected, mixed $actual): void
    {
        if ($expected instanceof Closure) {
            $this->assertInstanceOf(Closure::class, $actual);
            $this->assertSame($expected(), $actual());

            return;
        }

        if (is_array($expected)) {
            $this->assertIsArray($actual);
            $this->assertSame(array_keys($expected), array_keys($actual));

            foreach ($expected as $key => $value) {
                $this->assertSameResults($value, $actual[$key]);
            }

            return;
        }

        $this->assertSame($expected, $actual);
    }

    protected function tearDown(): void
    {
        $filename = './tests/Fixtures/tmp.config';
        if (file_exists($filename)) {
            unlink('./tests/Fixtures/tmp.config');
        }
    }
}

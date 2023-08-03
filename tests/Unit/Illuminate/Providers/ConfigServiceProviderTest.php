<?php

namespace Henzeb\VarExportWrapper\Tests\Unit\Illuminate\Providers;

use Closure;
use Henzeb\VarExportWrapper\Illuminate\Providers\ConfigServiceProvider;
use Henzeb\VarExportWrapper\Tests\Fixtures\ExportableClass;
use Henzeb\VarExportWrapper\Tests\Fixtures\RegularClass;
use Henzeb\VarExportWrapper\VarExportable;
use Orchestra\Testbench\TestCase;
use function Henzeb\VarExportWrapper\Support\Functions\exportify;

class ConfigServiceProviderTest extends TestCase
{
    private array $oldArgv = [];
    protected function setUp(): void
    {
        $this->oldArgv = $_SERVER['argv'];
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set([
            'export' => [
                'closure' => fn() => 'hello world',
                'exportableClass' => new ExportableClass(),
                'regularClass' => new RegularClass(),
                'exportable' => exportify(fn()=>'hello world')
            ]
        ]);
    }

    public function testShouldPrepareConfigForCache() {
        $_SERVER['argv'][1] = 'somethingElse';
        (new ConfigServiceProvider($this->app))->register();

        $this->assertInstanceOf(Closure::class, config('export.closure'));
        $this->assertInstanceOf(ExportableClass::class, config('export.exportableClass'));
        $this->assertInstanceOf(RegularClass::class, config('export.regularClass'));

        $this->assertInstanceOf(VarExportable::class, config('export.exportable'));
        $this->assertInstanceOf(Closure::class, config('export.exportable')->getObject());
        $_SERVER['argv'][1] = 'config:cache';

        (new ConfigServiceProvider($this->app))->register();

        $this->assertInstanceOf(VarExportable::class, config('export.closure'));
        $this->assertEquals('hello world', config('export.closure')->getObject()());

        $this->assertInstanceOf(ExportableClass::class, config('export.exportableClass'));
        $this->assertInstanceOf(VarExportable::class, config('export.regularClass'));

        $this->assertInstanceOf(VarExportable::class, config('export.exportable'));
        $this->assertInstanceOf(Closure::class, config('export.exportable')->getObject());
    }

    public function testShouldPrepareConfigForCacheWithOptimize() {

        $_SERVER['argv'][1] = 'optimize';

        (new ConfigServiceProvider($this->app))->register();

        $this->assertInstanceOf(VarExportable::class, config('export.closure'));
        $this->assertEquals('hello world', config('export.closure')->getObject()());

        $this->assertInstanceOf(ExportableClass::class, config('export.exportableClass'));
        $this->assertInstanceOf(VarExportable::class, config('export.regularClass'));

        $this->assertInstanceOf(VarExportable::class, config('export.exportable'));
        $this->assertInstanceOf(Closure::class, config('export.exportable')->getObject());
    }

    protected function tearDown(): void
    {
        $_SERVER['argv'] = $this->oldArgv;
        parent::tearDown();
    }
}

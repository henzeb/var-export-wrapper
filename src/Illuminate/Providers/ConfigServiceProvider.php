<?php

namespace Henzeb\VarExportWrapper\Illuminate\Providers;

use ArrayAccess;
use Henzeb\VarExportWrapper\VarExportable;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Console\ConfigCacheCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use function Henzeb\VarExportWrapper\Support\Functions\exportify;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (($_SERVER['argv'][1] ?? null) === (ConfigCacheCommand::getDefaultName() ?? 'config:cache')) {
            exportify(
                Config::getFacadeRoot()
            );
        }
    }
}

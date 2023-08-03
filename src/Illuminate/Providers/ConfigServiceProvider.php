<?php

namespace Henzeb\VarExportWrapper\Illuminate\Providers;

use Illuminate\Foundation\Console\ConfigCacheCommand;
use Illuminate\Foundation\Console\OptimizeCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use function Henzeb\VarExportWrapper\Support\Functions\exportify;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $commands = [
            ConfigCacheCommand::getDefaultName() ?? 'config:cache',
            OptimizeCommand::getDefaultName() ?? 'optimize'
        ];

        if (!empty($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], $commands)) {
            exportify(
                Config::getFacadeRoot()
            );
        }
    }
}

<?php

namespace Henzeb\VarExportWrapper\Illuminate\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

use function Henzeb\VarExportWrapper\Support\Functions\exportify;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $argv = $_SERVER['argv'] ?? [];

        if (is_array($argv) && in_array($argv[1] ?? null, ['config:cache', 'optimize'], true)) {
            exportify(
                Config::getFacadeRoot()
            );
        }
    }
}

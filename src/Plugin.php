<?php

namespace ArchiElite\IpBlocker;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function removed(): void
    {
        Schema::dropIfExists('ip_blocker_logs');
    }
}

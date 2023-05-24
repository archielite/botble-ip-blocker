<?php

namespace ArchiElite\IpBlocker\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\Form;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class IpBlockerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/ip-blocker')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations();

        Event::listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-ip-blocker',
                'priority' => 1001,
                'parent_id' => 'cms-core-settings',
                'name' => 'plugins/ip-blocker::ip-blocker.menu',
                'url' => route('ip-blocker.loadIpBlocker'),
                'permissions' => ['ip-blocker.loadIpBlocker'],
            ]);
        });

        Form::component('tags', 'plugins/ip-blocker::forms.tags', [
            'name',
            'value' => null,
            'attributes' => [],
        ]);
    }
}

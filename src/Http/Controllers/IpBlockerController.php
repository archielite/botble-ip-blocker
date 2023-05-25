<?php

namespace ArchiElite\IpBlocker\Http\Controllers;

use ArchiElite\IpBlocker\Http\Requests\UpdateSettingsRequest;
use ArchiElite\IpBlocker\Tables\HistoryTable;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class IpBlockerController extends BaseController
{
    public function settings(Request $request, HistoryTable $historyTable)
    {
        PageTitle::setTitle(trans('plugins/ip-blocker::ip-blocker.menu'));

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
            ->addScriptsDirectly([
                'vendor/core/core/base/libraries/tagify/tagify.js',
                'vendor/core/core/base/js/tags.js',
            ]);

        $ips = implode(',', json_decode(setting('ip_blocker_addresses'), true));

        if ($request->expectsJson()) {
            return $historyTable->renderTable();
        }

        return view('plugins/ip-blocker::settings', compact('ips', 'historyTable'));
    }

    public function updateSettings(UpdateSettingsRequest $request, BaseHttpResponse $response)
    {
        $data = $request->input('ip_addresses');

        $localIp = $request->ip();

        foreach ($data as $key => $value) {
            if ($value['value'] === $localIp) {
                unset($data[$key]);
            }
        }

        setting()->set('ip_blocker_addresses', json_encode(collect($data)->pluck('value')))->save();

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.update_settings_success'));
    }
}

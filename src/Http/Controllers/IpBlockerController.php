<?php

namespace ArchiElite\IpBlocker\Http\Controllers;

use ArchiElite\IpBlocker\Http\Requests\AvailableCountriesRequest;
use ArchiElite\IpBlocker\Http\Requests\CheckSecretKeyRequest;
use ArchiElite\IpBlocker\Http\Requests\UpdateSettingsRequest;
use ArchiElite\IpBlocker\Models\History;
use ArchiElite\IpBlocker\Repositories\Interfaces\IpBlockerInterface;
use ArchiElite\IpBlocker\Tables\HistoryTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Traits\HasDeleteManyItemsTrait;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IpBlockerController extends BaseController
{
    use HasDeleteManyItemsTrait;

    public function __construct(protected IpBlockerInterface $ipBlockerRepository)
    {
    }

    public function settings(Request $request, HistoryTable $historyTable)
    {
        PageTitle::setTitle(trans('plugins/ip-blocker::ip-blocker.menu'));

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
            ->addScriptsDirectly([
                'vendor/core/core/setting/js/setting.js',
                'vendor/core/core/base/libraries/tagify/tagify.js',
                'vendor/core/core/base/js/tags.js',
            ]);

        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            Assets::addScriptsDirectly('vendor/core/plugins/location/js/location.js');
        }

        $ips = implode(',', json_decode(setting('ip_blocker_addresses'), true));

        $ipsRange = implode(',', json_decode(setting('ip_blocker_addresses_range'), true));

        $secret_key = setting('ip_blocker_secret_key');

        $countriesCode = json_decode(setting('ip_blocker_available_countries'), true);

        if ($request->expectsJson()) {
            return $historyTable->renderTable();
        }

        return view('plugins/ip-blocker::settings', compact('ips', 'ipsRange', 'secret_key', 'countriesCode', 'historyTable'));
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

        $ipsRange = $request->input('ip_addresses_range');

        $localIpsRange = explode('.', $localIp);

        $formatLocalIpsRange = implode('.', [
            $localIpsRange[0],
            $localIpsRange[1],
        ]);

        foreach ($ipsRange as $key => $value) {
            if (str_starts_with(trim($value['value'], '*'), $formatLocalIpsRange)) {
                unset($ipsRange[$key]);
            }
        }

        setting()->set('ip_blocker_addresses_range', json_encode(collect($ipsRange)->pluck('value')))->save();

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.update_settings_success'));
    }

    public function checkSecretKey(CheckSecretKeyRequest $request, BaseHttpResponse $response)
    {
        $secretKey = $request->input('secret_key');

        $data = Http::get("https://ipinfo.io?token=$secretKey");

        if ($data->ok()) {
            setting()->set('ip_blocker_secret_key', $secretKey)->save();
            setting()->set('ip_blocker_available_countries', '[]')->save();

            return $response
                ->setNextUrl(route('ip-blocker.settings'))
                ->setMessage(trans('plugins/ip-blocker::ip-blocker.activation_succeeded'));
        }

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setError()
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.activation_failed'));
    }

    public function availableCountries(AvailableCountriesRequest $request, BaseHttpResponse $response)
    {
        $data = json_encode($request->input('available_countries'));

        setting()->set('ip_blocker_available_countries', $data)->save();

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.update_settings_success'));
    }

    public function destroy(History $ipBlocker, Request $request, BaseHttpResponse $response)
    {
        try {
            $this->ipBlockerRepository->delete($ipBlocker);

            event(new DeletedContentEvent(IP_BLOCKER_MODULE_SCREEN_NAME, $request, $ipBlocker));

            return $response->setMessage(trans('plugins/ip-blocker::ip-blocker.delete_success'));
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        return $this->executeDeleteItems($request, $response, $this->ipBlockerRepository, IP_BLOCKER_MODULE_SCREEN_NAME);
    }

    public function deleteAll(BaseHttpResponse $response)
    {
        $this->ipBlockerRepository->getModel()->truncate();

        return $response->setMessage(trans('plugins/ip-blocker::ip-blocker.delete_success'));
    }
}

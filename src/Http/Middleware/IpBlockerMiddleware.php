<?php

namespace ArchiElite\IpBlocker\Http\Middleware;

use ArchiElite\IpBlocker\Models\History;
use Botble\Base\Supports\Helper;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Session;

class IpBlockerMiddleware
{
    protected function getIps(): array
    {
        return json_decode(setting('ip_blocker_addresses'), true);
    }

    protected function getIpsRange(): array
    {
        return json_decode(setting('ip_blocker_addresses_range'), true);
    }

    protected function getIpsByCountryCode(): array
    {
        return json_decode(setting('ip_blocker_available_countries'), true);
    }

    protected function checkIpRange(): bool
    {
        $clientIp = request()->getClientIp();

        foreach ($this->getIpsRange() as $ip) {
            if (str_starts_with($clientIp, trim($ip, '*'))) {
                return false;
            }
        }

        return true;
    }

    protected function checkIpsByCountryCode(): bool
    {
        $systemCountriesCode = array_keys(Helper::countries());

        $countriesCode = $this->getIpsByCountryCode();

        if (empty($countriesCode) || empty(array_diff($systemCountriesCode, $countriesCode))) {
            return true;
        }

        $secretKey = setting('ip_blocker_secret_key');

        if (! $secretKey) {
            return true;
        }

        $clientIp = Helper::getIpFromThirdParty();

        $sessionKey = 'check_response_' . md5($clientIp);

        if (Session::has($sessionKey)) {
            return Session::get($sessionKey);
        }

        $response = Http::get("https://ipinfo.io/$clientIp/country?token=$secretKey");

        if ($response->failed()) {
            Session::put($sessionKey, true);

            return true;
        }

        $responseCountryCode = trim($response->body());

        $isBlocked = in_array($responseCountryCode, $countriesCode, true);

        Session::put($sessionKey, $isBlocked);

        return $isBlocked;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && is_in_admin()) {
            return $next($request);
        }

        if (
            in_array($request->getClientIp(), $this->getIps()) ||
            $this->checkIpRange() === false ||
            $this->checkIpsByCountryCode() === false
        ) {
            History::query()->updateOrCreate([
                'ip_address' => $request->getClientIp(),
            ])->increment('count_requests');

            return response()->view('plugins/ip-blocker::errors.403', [
                'code' => 403,
                'message' => trans('plugins/ip-blocker::ip-blocker.message'),
            ]);
        }

        return $next($request);
    }
}

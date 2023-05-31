<?php

namespace ArchiElite\IpBlocker;

use Botble\Base\Supports\Helper;
use Illuminate\Support\Facades\Http;
use Session;

class IpBlocker
{
    public static function getSettings(): array
    {
        return [
            'ip' => setting('ip_blocker_addresses'),
            'ip_range' => setting('ip_blocker_addresses_range'),
            'allowed_countries' => setting('ip_blocker_available_countries'),
            'secret_key' => setting('ip_blocker_secret_key'),
        ];
    }

    public static function checkIpsRange(): bool
    {
        $ipRange = self::getSettings()['ip_range'];

        if (! $ipRange) {
            return true;
        }

        $ipRange = json_decode($ipRange, true);

        if (! $ipRange) {
            return true;
        }

        $clientIp = request()->ip();

        $explodeClientIp = explode('.', $clientIp);

        $formatClientIp = implode('.', [
            $explodeClientIp[0],
            $explodeClientIp[1],
        ]);

        foreach ($ipRange as $ip) {
            if (str_starts_with($formatClientIp, substr($ip, 0, -2))) {
                return false;
            }
        }

        return true;
    }

    public static function checkIpsByCountryCode(): bool
    {
        $systemCountriesCode = array_keys(Helper::countries());

        $allowedCountries = self::getSettings()['allowed_countries'];

        if (! $allowedCountries) {
            return true;
        }

        $allowedCountries = json_decode($allowedCountries, true);

        if (! $allowedCountries) {
            return true;
        }

        if (empty(array_diff($systemCountriesCode, $allowedCountries))) {
            return true;
        }

        $sessionKey = 'ip_blocker_response_cache_' . md5(json_encode(self::getSettings()));

        if (Session::has($sessionKey)) {
            return Session::get($sessionKey);
        }

        $response = self::callAPI();

        if (! $response) {
            Session::put($sessionKey, true);

            return true;
        }

        $isBlocked = in_array($response['country'], $allowedCountries, true);

        Session::put($sessionKey, $isBlocked);

        return $isBlocked;
    }

    public static function callAPI(): array
    {
        $secretKey = self::getSettings()['secret_key'];

        if (! $secretKey) {
            return [];
        }

        $cacheKey = 'ip_blocker_cache_responses_' . md5(json_encode(self::getSettings()));

        if (Session::has($cacheKey) && $data = Session::get($cacheKey)) {
            return $data;
        }

        $response = Http::withoutVerifying()->asJson()->get("https://ipinfo.io?token=$secretKey");

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();

        Session::put($cacheKey, $data);

        return $data;
    }
}

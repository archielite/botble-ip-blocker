<?php

namespace ArchiElite\IpBlocker\Http\Middleware;

use ArchiElite\IpBlocker\IpBlocker;
use ArchiElite\IpBlocker\Models\History;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class IpBlockerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && is_in_admin()) {
            return $next($request);
        }

        $response = IpBlocker::callAPI();

        if (
            (! $response || in_array($response['ip'], json_decode(IpBlocker::getSettings()['ip'], true)))
            || IpBlocker::checkIpsRange() === false
            || IpBlocker::checkIpsByCountryCode() === false
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

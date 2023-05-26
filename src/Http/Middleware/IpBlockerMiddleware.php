<?php

namespace ArchiElite\IpBlocker\Http\Middleware;

use ArchiElite\IpBlocker\Models\History;
use Illuminate\Http\Request;
use Closure;

class IpBlockerMiddleware
{
    protected function getIps(): array
    {
        return json_decode(setting('ip_blocker_addresses'), true);
    }

    protected function checkIpRange(): bool
    {
        foreach ($this->getIps() as $ip) {
            if (str_starts_with(request()->getClientIp(), trim($ip, '*'))) {
                return true;
            }
        }

        return false;
    }

    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->getClientIp(), $this->getIps())) {
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

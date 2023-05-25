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

    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->getClientIp(), $this->getIps())) {
            History::query()->updateOrCreate([
                'ip_address' => $request->getClientIp(),
            ])->increment('count');

            abort(403);
        }

        return $next($request);
    }
}

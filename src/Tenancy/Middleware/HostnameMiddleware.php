<?php

namespace Hyn\Tenancy\Middleware;

use Closure;

class HostnameMiddleware
{
    public function handle($request, Closure $next)
    {
        /* @var \Hyn\Tenancy\Models\Hostname */
        $hostname = app('tenant.hostname');
        if ($hostname && ! is_null($redirect = $hostname->redirectActionRequired())) {
            return $redirect;
        }
        
        if (!$hostname) {
            return response('Not Found', 404);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAccess
{
    /**
     * Jaga akses dashboard monitoring (/pulse, /telescope, /horizon).
     *
     * Middleware Sentinel pada route dashboard dibuat default-mengizinkan
     * (membuka jalan di belakang reverse proxy), sehingga penegakan permission
     * sesungguhnya berada di sini: hanya pengguna dengan permission
     * monitoring.monitoring.lihat yang boleh membuka dashboard. Super-admin
     * otomatis lolos melalui hook Gate::before di AppServiceProvider.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->can('monitoring.monitoring.lihat'), 403);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->company_id) {
            return redirect()->route('landing')
                ->with('error', __('app.no_company'));
        }

        return $next($request);
    }
}

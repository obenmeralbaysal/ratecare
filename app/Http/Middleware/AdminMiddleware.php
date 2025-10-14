<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $superUser = getImpersonatingSuperUser();
    
        if ($superUser && $superUser->is_admin)
            switchBackToSuperUser();
        
        if (user()->is_admin == 0) {
            return redirect(url("/"));
        }

        return $next($request);
    }
}

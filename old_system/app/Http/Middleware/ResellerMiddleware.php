<?php

namespace App\Http\Middleware;

use Closure;

class ResellerMiddleware
{
    /**
     * only let reseller users pass through
     * also reinstate the reseller if one is impersonating a sub user at the moment
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $superUser = getImpersonatingSuperUser();
    
        if ($superUser && isReseller($superUser))
            switchBackToSuperUser();
        
        // check if the user is a reseller
        if (user(true)->user_type != 2) {
            return redirect(url("/"));
        }
        
        return $next($request);
    }
}

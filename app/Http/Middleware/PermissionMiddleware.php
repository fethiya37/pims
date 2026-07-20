<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
 
    
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check()){
            $permission = Role::where('id',Auth::user()->role_id)->first();
            if( $permission != null){
                return $next($request);
            }else{
                return back()->with('error','Access Denied.!!! Your Role Not Set Yet.');
            }

        }
    }
}

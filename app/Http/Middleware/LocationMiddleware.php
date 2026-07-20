<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationMiddleware
{
   
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check()){
            $permission = Role::where('id',Auth::user()->role_id)->first();
                if($permission->manage_locations  == 'on'){
                    return $next($request);
                }else{
                    return back()->with('error','Access Denied.!!! Please Contact Supper ADMIN.');
                }
            }
    }
}

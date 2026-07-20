<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageInventoryTransferMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $permission = Role::where('id', Auth::user()->role_id)->first();
            if ($permission->manage_inventory_transfer == 'on') {
                return $next($request);
            }
            return back()->with('error', 'Access Denied. Please Contact Super Admin.');
        }
        return redirect()->route('login');
    }
}
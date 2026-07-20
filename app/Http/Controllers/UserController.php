<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('created_at', 'desc')->get();
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $users = User::orderBy('created_at', 'desc')->get();
            $locations = Location::orderBy('id', 'desc')->get();
        } else {
            if ($user->location) {
                $users = User::whereHas('location', function ($query) use ($user) {
                    $query->where('type', $user->location->type);
                })->orderBy('created_at', 'desc')->get();

                $locations = Location::where('type', $user->location->type)
                    ->orderBy('id', 'desc')->get();
            } else {
                $users = collect();
                $locations = collect();
            }
        }

        $permissions = config('permissions');

        return view('pages.user.users', compact('roles', 'users', 'locations', 'permissions'));
    }

    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location_id' => $request->location_id,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'User registered successfully.');
    }

    public function editUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location_id' => $request->location_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        User::where('id', $id)->update($data);

        return back()->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting your own account
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
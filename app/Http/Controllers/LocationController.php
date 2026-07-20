<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $locations = Location::orderBy('id', 'desc')->get();
        } else {
            $locations = Location::where('type', $user->location->type ?? null)
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('pages.locations.location', compact('locations'));
    }

    public function addLocation(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'type' => ['required', 'in:store,point_of_use'],
        ]);

        Location::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return back()->with('success', 'Location Added Successfully.');
    }

    public function editLocation(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'type' => ['required', 'in:store,point_of_use'],
        ]);

        Location::where('id', $id)->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return back()->with('success', 'Location Updated Successfully.');
    }

    public function deleteLocation($id)
    {
        Location::where('id', $id)->delete();
        return back()->with('success', 'Location Deleted Successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductShelfLocation;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class ShelfController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            // Super Admin sees everything
            $shelves = Shelf::with('location')->get();
            $locations = Location::all();
            $records = ProductShelfLocation::with('product', 'shelf.location')->get();
        } else {
            // Restrict to the user’s location type
            $locationType = $user->location->type ?? null;

            $shelves = Shelf::with('location')
                ->whereHas('location', function ($q) use ($locationType) {
                    $q->where('type', $locationType);
                })
                ->get();

            $locations = Location::where('type', $locationType)->get();

            $records = ProductShelfLocation::with('product', 'shelf.location')
                ->whereHas('shelf.location', function ($q) use ($locationType) {
                    $q->where('type', $locationType);
                })
                ->get();
        }

        $products = Product::all();

        return view('pages.shelves.shelf', compact('shelves', 'products', 'locations', 'records'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'code' => [
                'required',
                'string',
                'max:255',
                // Ensure combination of location_id + code is unique
                Rule::unique('shelves')->where(function ($query) use ($request) {
                    return $query->where('location_id', $request->location_id);
                }),
            ],
        ], [
            'code.unique' => 'This shelf code already exists for the selected location.',
        ]);

        Shelf::create([
            'location_id' => $request->location_id,
            'code' => $request->code,
        ]);

        return back()->with('success', 'Shelf added successfully.');
    }

    public function update(Request $request, $id)
    {
        $shelf = Shelf::findOrFail($id);

        $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'code' => [
                'required',
                'string',
                'max:255',
                // Unique combination except for current record
                Rule::unique('shelves')->ignore($shelf->id)->where(function ($query) use ($request) {
                    return $query->where('location_id', $request->location_id);
                }),
            ],
        ], [
            'code.unique' => 'This shelf code already exists for the selected location.',
        ]);

        $shelf->update([
            'location_id' => $request->location_id,
            'code' => $request->code,
        ]);

        return back()->with('success', 'Shelf updated successfully.');
    }

    public function destroy($id)
    {
        Shelf::destroy($id);
        return back()->with('success', 'Shelf deleted successfully.');
    }
}

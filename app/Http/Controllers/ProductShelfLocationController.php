<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductShelfLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ProductShelfLocationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                // Unique combination of product_id + shelf_id
                Rule::unique('product_shelf_locations')->where(function ($query) use ($request) {
                    return $query->where('shelf_id', $request->shelf_id);
                }),
            ],
            'shelf_id' => 'required|exists:shelves,id',
        ], [
            'product_id.unique' => 'This product is already assigned to the selected shelf.',
        ]);

        ProductShelfLocation::create($request->only(['product_id', 'shelf_id']));

        return back()->with('success', 'Record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $record = ProductShelfLocation::findOrFail($id);

        $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                // Unique combination except for current record
                Rule::unique('product_shelf_locations')->ignore($record->id)->where(function ($query) use ($request) {
                    return $query->where('shelf_id', $request->shelf_id);
                }),
            ],
            'shelf_id' => 'required|exists:shelves,id',
        ], [
            'product_id.unique' => 'This product is already assigned to the selected shelf.',
        ]);

        $record->update($request->only(['product_id', 'shelf_id']));

        return back()->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        ProductShelfLocation::destroy($id);
        return back()->with('success', 'Record deleted successfully.');
    }
}
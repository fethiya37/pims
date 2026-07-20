<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    
    public function addCategory(Request $request): RedirectResponse
    {
        Category::create([
            'name' => $request->category_name,
            'description' => $request->description,
        ]);
        return back()->with('success', 'Category Added Successfully.');
    }

    public function editCategory(Request $request, $id)
    {
        Category::where('id', $id)->update([
            'name' => $request->category_name,
        ]);
        return back()->with('success', 'Update Successfully.');
    }

    public function deleteCategory($id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Category Deleted Successfully.');
    }

}

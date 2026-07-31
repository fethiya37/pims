<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function addCategory(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->category_name,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category added successfully.',
                'category' => $category
            ]);
        }

        return back()->with('success', 'Category Added Successfully.');
    }

    public function editCategory(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->category_name]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        }

        return back()->with('success', 'Update Successfully.');
    }

    public function deleteCategory($id): JsonResponse|RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return back()->with('success', 'Category Deleted Successfully.');
    }
}
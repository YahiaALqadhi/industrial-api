<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->latest()
            ->get()
            ->map(function ($category) {
                return $this->formatCategory($category);
            });

        return response()->json($categories);
    }

    public function show(string $slug)
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['parent', 'children'])
            ->firstOrFail();

        return response()->json($this->formatCategory($category, true));
    }

    private function formatCategory(Category $category, bool $includeParent = false): array
    {
        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'parent' => $includeParent && $category->parent ? [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ] : null,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image ? asset('storage/' . $category->image) : null,
            'description' => $category->description,
            'is_active' => (bool) $category->is_active,
            'children' => $category->children->map(function ($child) {
                return $this->formatCategory($child);
            })->values(),
        ];
    }
}
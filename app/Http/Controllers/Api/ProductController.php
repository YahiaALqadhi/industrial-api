<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category.parent.parent', 'images'])
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $categoryId = $request->integer('category_id');

            $categoryIds = $this->collectCategoryIds($categoryId);

            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', (bool) $request->featured);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query
            ->latest()
            ->paginate(12)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'category_path' => $this->buildCategoryPath($product->category),
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'brand' => $product->brand,
                    'price' => (float) $product->price,
                    'discount_price' => $product->discount_price !== null ? (float) $product->discount_price : null,
                    'stock' => $product->stock,
                    'main_image' => $product->main_image ? asset('storage/' . $product->main_image) : null,
                    'short_description' => $product->short_description,
                    'is_active' => (bool) $product->is_active,
                    'is_featured' => (bool) $product->is_featured,
                ];
            });

        return response()->json($products);
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with(['category.parent.parent', 'images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'id' => $product->id,
            'category' => [
                'id' => $product->category?->id,
                'name' => $product->category?->name,
                'slug' => $product->category?->slug,
                'path' => $this->buildCategoryPath($product->category),
            ],
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'brand' => $product->brand,
            'price' => (float) $product->price,
            'discount_price' => $product->discount_price !== null ? (float) $product->discount_price : null,
            'stock' => $product->stock,
            'main_image' => $product->main_image ? asset('storage/' . $product->main_image) : null,
            'gallery' => $product->images->sortBy('sort_order')->values()->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => $image->image ? asset('storage/' . $image->image) : null,
                    'sort_order' => $image->sort_order,
                ];
            })->values(),
            'short_description' => $product->short_description,
            'description' => $product->description,
            'is_active' => (bool) $product->is_active,
            'is_featured' => (bool) $product->is_featured,
            'created_at' => $product->created_at,
        ]);
    }

    private function collectCategoryIds(int $categoryId): array
    {
        $category = Category::with('children')->find($categoryId);

        if (! $category) {
            return [$categoryId];
        }

        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child->id));
        }

        return array_values(array_unique($ids));
    }

    private function buildCategoryPath(?Category $category): array
    {
        if (! $category) {
            return [];
        }

        $path = [];

        while ($category) {
            array_unshift($path, [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

            $category = $category->parent;
        }

        return $path;
    }
}
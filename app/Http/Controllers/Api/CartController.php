<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with(['items.product'])
            ->firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json([
            'cart_id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'product_slug' => $item->product?->slug,
                    'product_image' => $item->product?->main_image ? asset('storage/' . $item->product->main_image) : null,
                    'price' => (float) $item->price,
                    'quantity' => $item->quantity,
                    'total' => (float) $item->total,
                    'stock' => $item->product?->stock,
                ];
            }),
            'subtotal' => (float) $cart->subtotal,
            'total_items' => $cart->total_items,
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = $data['quantity'] ?? 1;

        $product = Product::findOrFail($data['product_id']);

        if (! $product->is_active) {
            return response()->json([
                'message' => 'This product is not available.',
            ], 422);
        }

        if ($product->stock < $quantity) {
            return response()->json([
                'message' => 'Insufficient stock.',
            ], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $price = $product->discount_price ?: $product->price;

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + $quantity;

            if ($product->stock < $newQuantity) {
                return response()->json([
                    'message' => 'Insufficient stock for requested quantity.',
                ], 422);
            }

            $item->update([
                'quantity' => $newQuantity,
                'price' => $price,
            ]);
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart.',
            'item_id' => $item->id,
        ], 201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = $cartItem->product;

        if (! $product || $product->stock < $data['quantity']) {
            return response()->json([
                'message' => 'Insufficient stock.',
            ], 422);
        }

        $cartItem->update([
            'quantity' => $data['quantity'],
            'price' => $product->discount_price ?: $product->price,
        ]);

        return response()->json([
            'message' => 'Cart item updated successfully.',
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart.',
        ]);
    }

    public function clear(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }
}
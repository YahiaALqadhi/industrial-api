<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $address = Address::where('id', $data['address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cart = Cart::with(['items.product'])->firstOrCreate([
            'user_id' => $user->id,
        ]);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                return response()->json([
                    'message' => 'One or more products are unavailable.',
                ], 422);
            }

            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $item->product->name,
                ], 422);
            }
        }

        $currency = Setting::getValue('currency', 'USD');

        $order = DB::transaction(function () use (
            $user,
            $address,
            $cart,
            $data,
            $currency
        ) {
            $order = Order::create([
                'user_id' => $user->id,
                'shipping_per_item' => 0,
                'shipping_cost' => 0,
                'discount_amount' => 0,
                'currency' => $currency,
                'status' => 'pending',
                'payment_status' => 'pending',
                'customer_name' => $address->recipient_name,
                'customer_email' => $user->email,
                'customer_phone' => $address->phone,
                'country' => $address->country,
                'city' => $address->city,
                'shipping_address' => trim(
                    $address->address_line_1 .
                    ($address->address_line_2 ? ', ' . $address->address_line_2 : '') .
                    ($address->state ? ', ' . $address->state : '') .
                    ($address->postal_code ? ', ' . $address->postal_code : '')
                ),
                'notes' => $data['notes'] ?? null,
                'ordered_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $item->price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->price * $item->quantity,
                    'product_image' => $product->main_image,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $order->refresh();
            $order->calculateTotals();
            $order->save();

            $cart->items()->delete();

            return $order;
        });

        User::adminRecipients()->each(function ($admin) use ($order) {
            $admin->notify(new NewOrderNotification($order));
        });

        return response()->json([
            'message' => 'Order submitted successfully.',
            'order_id' => $order->id,
        ], 201);
    }

    public function myOrders(Request $request)
    {
        return response()->json(
            Order::with('items')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get()
        );
    }

    public function showMyOrder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load('items');

        return response()->json($order);
    }

    public function cancelMyOrder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $order->canBeCancelledByCustomer()) {
            return response()->json([
                'message' => 'This order can no longer be cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->load('items.product');

            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
            ]);
        });

        return response()->json([
            'message' => 'Order cancelled successfully.',
        ]);
    }

    // 🔥🔥🔥 التعديل الحقيقي هنا
    public function updateMyOrder(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Order cannot be edited. Contact support.',
            ], 422);
        }

        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        $address = Address::where('id', $data['address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($order, $data, $address) {

            $order->load('items.product');

            foreach ($data['items'] as $itemData) {

                $item = $order->items->firstWhere('id', $itemData['id']);
                if (! $item || ! $item->product) continue;

                $product = $item->product;

                $oldQty = $item->quantity;
                $newQty = $itemData['quantity'];

                $diff = $newQty - $oldQty;

                // 🔥 زيادة
                if ($diff > 0) {
                    if ($product->stock < $diff) {
                        abort(422, "Insufficient stock for {$product->name}");
                    }
                    $product->decrement('stock', $diff);
                }

                // 🔥 نقصان
                if ($diff < 0) {
                    $product->increment('stock', abs($diff));
                }

                $item->update([
                    'quantity' => $newQty,
                    'total_price' => $newQty * $item->unit_price,
                ]);
            }

            $order->update([
                'customer_name' => $address->recipient_name,
                'customer_phone' => $address->phone,
                'country' => $address->country,
                'city' => $address->city,
                'shipping_address' => trim(
                    $address->address_line_1 .
                    ($address->address_line_2 ? ', ' . $address->address_line_2 : '') .
                    ($address->state ? ', ' . $address->state : '') .
                    ($address->postal_code ? ', ' . $address->postal_code : '')
                ),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->refresh();
            $order->calculateTotals();
            $order->save();
        });

        return response()->json([
            'message' => 'Order updated successfully.',
        ]);
    }

    public function uploadReceipt(Request $request, Order $order)
{
    if ($order->user_id !== $request->user()->id) {
        abort(403);
    }

    if ($order->status !== 'confirmed') {
        return response()->json([
            'message' => 'You can upload a payment receipt only after the order is confirmed.',
        ], 422);
    }

    $request->validate([
        'receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    $path = $request->file('receipt')->store('payment-receipts', 'public');

    $order->update([
        'payment_receipt' => $path,
        'payment_status' => 'pending',
    ]);

    return response()->json([
        'message' => 'Payment receipt uploaded successfully.',
        'payment_receipt' => asset('storage/' . $path),
    ]);
}
}
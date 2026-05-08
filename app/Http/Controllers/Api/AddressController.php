<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->addresses()->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string'],
            'address_line_2' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        if (!empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($data);

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string'],
            'address_line_2' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json([
            'message' => 'Address updated successfully.',
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }
}
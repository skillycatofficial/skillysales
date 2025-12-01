<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    /**
     * Display a listing of dealers
     */
    public function index()
    {
        $dealers = Dealer::with('user')->get();
        return response()->json($dealers);
    }

    /**
     * Store a newly created dealer (shop setup)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Check if user already has a dealer
        if ($user->dealer) {
            return response()->json([
                'error' => 'You already have a shop set up',
                'dealer' => $user->dealer,
            ], 400);
        }

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $dealer = Dealer::create([
            'user_id' => $user->id,
            'shop_name' => $validated['shop_name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_verified' => false,
        ]);

        return response()->json($dealer->load('user'), 201);
    }

    /**
     * Display the specified dealer
     */
    public function show($id)
    {
        $dealer = Dealer::with('user', 'cars')->findOrFail($id);
        return response()->json($dealer);
    }

    /**
     * Update the specified dealer
     */
    public function update(Request $request, $id)
    {
        $dealer = Dealer::findOrFail($id);
        
        // Check if user owns this dealer
        if ($dealer->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'shop_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|string|max:500',
            'phone' => 'sometimes|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $dealer->update($validated);
        return response()->json($dealer->load('user'));
    }

    /**
     * Remove the specified dealer
     */
    public function destroy($id)
    {
        $dealer = Dealer::findOrFail($id);
        $dealer->delete();
        return response()->json(['message' => 'Dealer deleted successfully']);
    }

    /**
     * Toggle dealer verification status (Admin)
     */
    public function toggleVerification($id)
    {
        $dealer = Dealer::with('user')->findOrFail($id);
        $dealer->is_verified = !$dealer->is_verified;
        $dealer->save();

        return response()->json($dealer);
    }
}

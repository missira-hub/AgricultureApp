<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // ✅ Ensure only farmers can access
    private function authorizeFarmer()
    {
        if (Auth::user()->role !== 'farmer') {
            abort(response()->json(['message' => 'Access denied. Farmers only.'], 403));
        }
    }

    // ✅ List all products owned by the authenticated farmer
    public function index()
    {
        $this->authorizeFarmer();

        $products = Product::where('user_id', Auth::id())->get();

        return response()->json($products);
    }

    // ✅ Store a new product for the authenticated farmer
    public function store(Request $request)
    {
        $this->authorizeFarmer();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
        ]);

        $validated['user_id'] = Auth::id();

        $exists = Product::where('user_id', $validated['user_id'])
            ->where('name', $validated['name']) // adjust field if needed
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Product already exists'], 409);
        }

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    // ✅ Show a product belonging to the authenticated farmer
    public function show($id)
    {
        $this->authorizeFarmer();

        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        return response()->json($product);
    }

    // ✅ Update a product owned by the authenticated farmer
    public function update(Request $request, $id)
{
    $this->authorizeFarmer();

    $product = Product::where('user_id', Auth::id())->findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'price' => 'sometimes|required|numeric',
        'quantity' => 'sometimes|required|integer',
    ]);

    $product->update($validated);

    // 🔁 Refresh to get latest values from DB
    $product->refresh();

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => $product
    ]);
}


    // ✅ Delete a product owned by the authenticated farmer
   public function destroy($id)
{
    $this->authorizeFarmer();

    // Try to find the product owned by the authenticated farmer
    $product = Product::where('user_id', Auth::id())->find($id);

    // If not found, return a meaningful error
    if (!$product) {
        return response()->json([
            'message' => 'Product not found or already deleted.'
        ], 404);
    }

    // Delete the product
    $product->delete();

    // Return confirmation message
    return response()->json([
        'message' => 'Product deleted successfully.',
        'product_id' => $id
    ], 200);
}

}

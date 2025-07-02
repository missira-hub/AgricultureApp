<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // List all products owned by the authenticated user
    public function index()
    {
        $userId = auth()->id();
        $products = Product::where('user_id', $userId)->get();
        return response()->json($products);
    }

    // Store a new product
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'quantity' => 'required|integer',
    ]);

  // Add the authenticated user's ID
$validated['user_id'] = auth()->id();

// Check if product with the same unique attribute already exists for this user
// Adjust the condition below according to your unique fields, e.g., 'name' or 'sku'
$exists = Product::where('user_id', $validated['user_id'])
    ->where('name', $validated['name'])  // replace 'name' with your unique field(s)
    ->exists();

if ($exists) {
    return response()->json(['message' => 'Product already exists'], 409); // Conflict HTTP status
}

// If not exists, create the product
$product = Product::create($validated);

return response()->json($product, 201);
}


    // Show a single product owned by the authenticated user
    public function show($id)
    {
        $userId = auth()->id();

        $product = Product::where('user_id', $userId)->findOrFail($id);

        return response()->json($product);
    }

    // Update a product owned by the authenticated user
    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        $product = Product::where('user_id', $userId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'quantity' => 'sometimes|required|integer',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    // Delete a product owned by the authenticated user
    public function destroy($id)
    {
        $userId = auth()->id();

        $product = Product::where('user_id', $userId)->findOrFail($id);

        $product->delete();

        return response()->json(null, 204);
    }
}

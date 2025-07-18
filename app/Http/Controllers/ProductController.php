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

public function index()
{
    $user = Auth::user();
    
    if ($user->role === 'farmer') {
        $products = Product::where('user_id', $user->id)->get();
    } else {
        $products = Product::all();
    }

    return response()->json($products);
}

    // ✅ Store a new product for the authenticated farmer
    public function store(Request $request)
{
    $farmer = Auth::user();

    $product = new Product();
    $product->name = $request->name;
    $product->description = $request->description;
    $product->price = $request->price;
    $product->quantity = $request->quantity;
    $product->user_id = $farmer->id; // 👈 Link to current farmer
    $product->save();

    return response()->json($product, 201);
}


    // ✅ Show a product belonging to the authenticated farmer
    public function show($id)
    {
        $this->authorizeFarmer();

        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        return response()->json($product);
    }public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $product->name = $request->input('name');
    $product->description = $request->input('description');
    $product->price = $request->input('price');
    $product->quantity = $request->input('quantity');

    if ($request->hasFile('image')) {
        // Store image in 'public/products'
        $path = $request->file('image')->store('products', 'public');
        // Save only the relative path or filename
        $product->image = $path;
    }

    $product->save();

    return response()->json($product);
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
public function feed(Request $request)
{
    $user = auth()->user();

    if ($user->role === 'farmer') {
        // Only show farmer's own products
        $products = Product::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    } elseif ($user->role === 'consumer') {
        // Show all products from all farmers
        $products = Product::whereHas('user', function ($query) {
                $query->where('role', 'farmer');
            })
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    } else {
        // Optional: deny access or return empty for other roles
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    return response()->json($products);
}
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
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

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'consumer') {
            $products = Product::with('category', 'user')
                ->whereHas('user', function ($q) {
                    $q->where('role', 'farmer');
                })
                ->latest()
                ->get();
        } else {
            // Farmers only see their own products

    $products = Product::with('unit', 'category')                 
    ->where('user_id', $user->id)  // changed here
                ->latest()
                ->get();
        }

        $products->transform(function ($product) {
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });

        // Note: This line overwrites $products; you might want to remove or adjust it:
        // $products = Product::with('category', 'user', 'unit')->get();

        return response()->json($products);
    }

    // ✅ Store a new product for the authenticated farmer
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'image' => 'nullable|image|max:10000000',
        ]);

        $product = new Product();
        $product->user_id = auth()->id();  // changed here from farmer_id to user_id
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->unit_id = $request->unit_id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ]);
    }

    // ✅ Show a product belonging to the authenticated farmer
    public function show($id)
    {
        $this->authorizeFarmer();

        $product = Product::where('user_id', Auth::id())->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'image' => 'nullable|image|max:100000000',
        ]);

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->unit_id = $request->unit_id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        return response()->json($product);
    }

    // ✅ Delete a product owned by the authenticated farmer
    public function destroy($id)
    {
        $this->authorizeFarmer();

        $product = Product::where('user_id', Auth::id())->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found or already deleted.'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $id
        ], 200);
    }
public function feed(Request $request)
{
    $user = auth()->user();

    // Guest users see all farmer products
    if (!$user) {
        $products = Product::with('category', 'user')
            ->whereHas('user', fn($q) => $q->where('role', 'farmer'))
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);
    } 
    // Farmer sees only their products
    elseif ($user->role === 'farmer') {
        $products = Product::with('category', 'user')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);
    } 
    // Consumer sees all farmer products
    elseif ($user->role === 'consumer') {
        $products = Product::with('category', 'user')
            ->whereHas('user', fn($q) => $q->where('role', 'farmer'))
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);
    } 
    else {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $products->getCollection()->transform(function ($product) {
        $product->image_url = $product->image ? asset('storage/'.$product->image) : null;
        return $product;
    });

    return response()->json($products);
}

public function search(Request $request)
{
    $query = $request->input('q');
    
    if (!$query) {
        return response()->json([]);
    }

    // Search by name or description (adjust as you want)
    $products = Product::where('name', 'LIKE', "%{$query}%")
                       ->orWhere('description', 'LIKE', "%{$query}%")
                       ->limit(20)
                       ->get();

    return response()->json($products);
}

}

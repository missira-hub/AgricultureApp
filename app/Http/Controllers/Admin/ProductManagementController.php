<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductManagementController extends Controller
{
    // List products with farmer info, paginated
    public function index()
    {
        $products = Product::with('user:id,name,email')  // load farmer info via 'user' relation
            ->latest()
            ->paginate(15);

        return response()->json($products);
    }

    // Delete a product by id
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Optionally delete the image file if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    // Update a product by id
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validate incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
            // Add other product fields as needed

            // For image file upload (optional)
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // If new image is uploaded, delete old one and store new
        if ($request->hasFile('image')) {
            // Delete old image file if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            // Store new image in 'products' folder in public disk
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        // Update product with validated data
        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }
    public function store(Request $request)
{
    // Validate incoming data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'nullable|exists:categories,id',
        'stock' => 'nullable|integer|min:0',
        // Add other product fields as needed

        // Optional image upload
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Handle image upload if exists
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('products', 'public');
        $validated['image'] = $path;
    }

    // Optionally assign the admin user as owner or use any farmer user id you want
    // For example, if your products table has a user_id column:
    // $validated['user_id'] = auth()->id();

    // Create new product
    $product = Product::create($validated);

    return response()->json([
        'message' => 'Product created successfully',
        'product' => $product,
    ]);
}

}

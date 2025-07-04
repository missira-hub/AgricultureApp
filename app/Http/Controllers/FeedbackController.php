<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    // 1. Submit feedback for a product (once per product per user)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string',
        ]);

        $userId = Auth::id();

        $exists = Feedback::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You already left feedback for this product.'], 422);
        }

        $feedback = Feedback::create([
            'user_id'    => $userId,
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return response()->json([
            'message' => 'Feedback submitted. Awaiting approval.',
            'data'    => $feedback
        ]);
    }

    // 2. Update feedback (only if you're the owner)
    public function update(Request $request, Feedback $feedback)
    {
        if ($feedback->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $feedback->update($request->only(['rating', 'comment']));

        return response()->json(['message' => 'Feedback updated.', 'data' => $feedback]);
    }

    // 3. Delete feedback
    public function destroy(Feedback $feedback)
    {
        if ($feedback->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted.']);
    }

    // 4. View approved feedback for a product
    public function productFeedback($productId)
    {
        $product = Product::findOrFail($productId);

        $feedback = Feedback::where('product_id', $productId)
            ->where('approved', true)
            ->with('user:id,name') // eager-load user name
            ->latest()
            ->paginate(10);

        return response()->json([
            'product_id' => $productId,
            'feedback'   => $feedback->items(),
            'pagination' => [
                'total'        => $feedback->total(),
                'per_page'     => $feedback->perPage(),
                'current_page' => $feedback->currentPage(),
                'last_page'    => $feedback->lastPage()
            ]
        ]);
    }

    // 5. View feedback for the farmer’s products
    public function farmerFeedback()
    {
        $farmer = Auth::user();

        $feedback = Feedback::whereHas('product', function ($q) use ($farmer) {
            $q->where('user_id', $farmer->id);
        })
            ->with('user:id,name', 'product:id,name') // optional details
            ->latest()
            ->paginate(10);

        return response()->json($feedback);
    }

    // 6. Farmer approves feedback
    public function approve(Request $request, Feedback $feedback)
    {
        if ($feedback->product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feedback->approved = true;
        $feedback->save();

        return response()->json(['message' => 'Feedback approved.']);
    }

    // 7. Farmer replies to feedback
    public function reply(Request $request, Feedback $feedback)
    {
        if ($feedback->product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['reply' => 'required|string']);

        $feedback->reply = $request->reply;
        $feedback->save();

        return response()->json(['message' => 'Reply added.', 'data' => $feedback]);
    }
}

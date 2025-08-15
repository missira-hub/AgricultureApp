<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized. Admins only.');
        }
    }

    // Admin view - paginated all feedbacks
    public function index()
    {
        $this->authorizeAdmin();

        $feedbacks = Feedback::with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($feedbacks);
    }

    // Delete a feedback (admin only)
    public function destroy($id)
    {
        $this->authorizeAdmin();

        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted']);
    }

    // Approve a feedback (admin only)
    public function approve($id)
    {
        $this->authorizeAdmin();

        $feedback = Feedback::findOrFail($id);
        $feedback->approved = true;
        $feedback->save();

        return response()->json(['message' => 'Feedback approved']);
    }

    // Public approved feedbacks (no admin check)
    public function approvedFeedbacks()
    {
        $feedbacks = Feedback::with([
            'product:id,name,image_url,user_id',
            'product.farmer:id,name',
            'user:id,name'
        ])
        ->where('approved', true)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($feedbacks);
    }
}

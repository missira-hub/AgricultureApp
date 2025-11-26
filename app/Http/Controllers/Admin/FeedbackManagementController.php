<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;

class FeedbackManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $feedback = Feedback::with(['user', 'product'])->paginate(20);
        return response()->json($feedback);
    }

    public function destroy(int $id): JsonResponse
    {
        Feedback::destroy($id);
        return response()->json(['message' => 'Feedback deleted']);
    }

    public function approve(int $id): JsonResponse
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->approved = true;
        $feedback->save();

        return response()->json(['message' => 'Feedback approved successfully']);
    }
}

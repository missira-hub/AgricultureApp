<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FarmerController extends Controller
{
    public function update(Request $request)
    {
        return response()->json(['message' => 'Farmer profile updated']);
    }
}

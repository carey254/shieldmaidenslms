<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRequest;
use App\Models\Activity;

class UserRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Submit a new request
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:course,role,access,support,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'priority' => 'in:low,medium,high,urgent'
        ]);

        $userRequest = UserRequest::createRequest(
            Auth::id(),
            $request->title,
            $request->description,
            $request->type
        );

        // Log the activity
        Activity::log(
            Auth::id(),
            'Request Submitted',
            "Submitted {$request->type} request: {$request->title}",
            'request',
            ['priority' => $request->priority]
        );

        return response()->json([
            'message' => 'Request submitted successfully',
            'request' => $userRequest
        ], 201);
    }

    /**
     * Get user's own requests
     */
    public function index()
    {
        $requests = UserRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'type' => $request->type,
                    'status' => $request->status,
                    'approved_at' => $request->approved_at,
                    'created_at' => $request->created_at->toISOString(),
                ];
            });

        return response()->json($requests);
    }
}

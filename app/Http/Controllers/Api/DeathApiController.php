<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Death;
use App\Models\Hospital;
use Illuminate\Http\Request;

class DeathApiController extends Controller
{
    /**
     * Retrieve death data for hospitals.
     */
    public function getDeathData(Request $request)
    {
        // Get token from Authorization header or request body
        $token = $request->header('Authorization') ?? $request->input('token');
        if ($token) {
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
        }

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Token is required in Authorization header (Bearer token) or request body'
            ], 401);
        }

        // Validate token in hospitals table
        $hospital = Hospital::where('token_api', $token)->where('is_active', true)->first();

        // Fallback to Sanctum auth if needed
        if (!$hospital && auth('sanctum')->check()) {
            $hospital = auth('sanctum')->user();
        }

        if (!$hospital) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid token or inactive hospital'
            ], 401);
        }

        // Fetch all death records
        $deaths = Death::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'hospital' => [
                'hospcode' => $hospital->hospcode,
                'name' => $hospital->name,
            ],
            'count' => $deaths->count(),
            'data' => $deaths
        ], 200);
    }
}

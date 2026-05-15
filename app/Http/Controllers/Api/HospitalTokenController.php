<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HospitalTokenController extends Controller
{
    // GET /hospitals/{hospcode}/tokens?page=&per_page=
    public function index(string $hospcode)
    {
        $hospital = Hospital::where('hospcode', $hospcode)->first();
        if (!$hospital) {
            return response()->json(['message' => 'Hospital not found'], 404);
        }

        $perPage = min((int) request('per_page', 20), 100);

        $tokens = $hospital->tokens()
            ->select('id', 'name', 'last_used_at', 'created_at', 'abilities')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'hospcode' => $hospital->hospcode,
            'tokens' => $tokens->items(),
            'meta' => [
                'current_page' => $tokens->currentPage(),
                'per_page'     => $tokens->perPage(),
                'total'        => $tokens->total(),
                'last_page'    => $tokens->lastPage(),
            ],
        ]);
    }

    // POST /hospitals/{hospcode}/tokens
    public function issue(Request $request, string $hospcode)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', 'max:50'],
        ]);

        $hospital = Hospital::firstOrCreate(
            ['hospcode' => $hospcode],
            ['name' => null, 'is_active' => true]
        );

        if (!$hospital->is_active) {
            return response()->json(['message' => 'Hospital is inactive'], 403);
        }

        $allowedAbilities = ['ingest']; // ขยายได้ตามต้องการ
        $abilities = array_values(array_intersect($validated['abilities'] ?? ['ingest'], $allowedAbilities));

        $token = $hospital->createToken($validated['name'], $abilities);

        // (ทางเลือก) log audit
        \Log::info('Hospital token issued', [
            'hospcode' => $hospital->hospcode,
            'token_id' => $token->accessToken->id,
            'abilities' => $abilities,
        ]);

        return response()->json([
            'hospcode' => $hospital->hospcode,
            'token_id' => $token->accessToken->id,
            'plain_text_token' => $token->plainTextToken, // โชว์ครั้งเดียว
            'abilities' => $abilities,
        ], 201);
    }

    // DELETE /hospitals/{hospcode}/tokens/{tokenId}
    public function revoke(string $hospcode, string $tokenId)
    {
        $hospital = Hospital::where('hospcode', $hospcode)->first();
        if (!$hospital) {
            return response()->json(['message' => 'Hospital not found'], 404);
        }

        $deleted = $hospital->tokens()->where('id', $tokenId)->delete();
        if ($deleted === 0) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        \Log::info('Hospital token revoked', [
            'hospcode' => $hospital->hospcode,
            'token_id' => $tokenId,
        ]);

        return response()->json(['message' => 'Token revoked'], 200);
        // หรือ return response()->noContent(); // 204
    }
}

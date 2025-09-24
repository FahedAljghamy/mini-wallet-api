<?php

/**
 * BeneficiaryController
 *
 * Controller for handling beneficiary management with proper validation
 * and API resource formatting
 *
 * @author Fahed
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeneficiaryRequest;
use App\Http\Requests\UpdateBeneficiaryRequest;
use App\Http\Resources\BeneficiaryResource;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    /**
     * Display a listing of user's beneficiaries
     * Author: Fahed
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->get('search');
        $favoritesOnly = $request->boolean('favorites_only');

        $query = $user->beneficiaries()
            ->with(['beneficiaryUser'])
            ->orderBy('is_favorite', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($search) {
            $query->search($search);
        }

        // Apply favorites filter
        if ($favoritesOnly) {
            $query->favorites();
        }

        $beneficiaries = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Beneficiaries retrieved successfully',
            'data' => [
                'beneficiaries' => BeneficiaryResource::collection($beneficiaries),
                'pagination' => [
                    'current_page' => $beneficiaries->currentPage(),
                    'last_page' => $beneficiaries->lastPage(),
                    'per_page' => $beneficiaries->perPage(),
                    'total' => $beneficiaries->total(),
                    'from' => $beneficiaries->firstItem(),
                    'to' => $beneficiaries->lastItem(),
                ],
                'filters' => [
                    'search' => $search,
                    'favorites_only' => $favoritesOnly,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created beneficiary
     * Author: Fahed
     */
    public function store(StoreBeneficiaryRequest $request): JsonResponse
    {
        $user = $request->user();
        $beneficiaryUser = User::where('email', $request->beneficiary_email)->first();

        $beneficiary = Beneficiary::create([
            'user_id' => $user->id,
            'beneficiary_user_id' => $beneficiaryUser->id,
            'nickname' => $request->nickname,
            'notes' => $request->notes,
            'is_favorite' => $request->boolean('is_favorite', false),
        ]);

        $beneficiary->load('beneficiaryUser');

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary added successfully',
            'data' => new BeneficiaryResource($beneficiary),
        ], 201);
    }

    /**
     * Display the specified beneficiary
     * Author: Fahed
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $beneficiary = $user->beneficiaries()
            ->with(['beneficiaryUser'])
            ->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary retrieved successfully',
            'data' => new BeneficiaryResource($beneficiary),
        ]);
    }

    /**
     * Update the specified beneficiary
     * Author: Fahed
     */
    public function update(UpdateBeneficiaryRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        $beneficiary = $user->beneficiaries()->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found',
            ], 404);
        }

        $beneficiary->update([
            'nickname' => $request->nickname,
            'notes' => $request->notes,
            'is_favorite' => $request->boolean('is_favorite', $beneficiary->is_favorite),
        ]);

        $beneficiary->load('beneficiaryUser');

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary updated successfully',
            'data' => new BeneficiaryResource($beneficiary),
        ]);
    }

    /**
     * Remove the specified beneficiary
     * Author: Fahed
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $beneficiary = $user->beneficiaries()->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found',
            ], 404);
        }

        $beneficiary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary removed successfully',
        ]);
    }

    /**
     * Toggle favorite status of a beneficiary
     * Author: Fahed
     */
    public function toggleFavorite(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $beneficiary = $user->beneficiaries()->find($id);

        if (!$beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found',
            ], 404);
        }

        $beneficiary->update([
            'is_favorite' => !$beneficiary->is_favorite,
        ]);

        $beneficiary->load('beneficiaryUser');

        return response()->json([
            'success' => true,
            'message' => $beneficiary->is_favorite ? 'Beneficiary marked as favorite' : 'Beneficiary removed from favorites',
            'data' => new BeneficiaryResource($beneficiary),
        ]);
    }

    /**
     * Get beneficiaries statistics
     * Author: Fahed
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalBeneficiaries = $user->beneficiaries()->count();
        $favoriteBeneficiaries = $user->beneficiaries()->favorites()->count();
        $recentBeneficiaries = $user->beneficiaries()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Beneficiaries statistics retrieved successfully',
            'data' => [
                'total_beneficiaries' => $totalBeneficiaries,
                'favorite_beneficiaries' => $favoriteBeneficiaries,
                'recent_beneficiaries' => $recentBeneficiaries,
                'regular_beneficiaries' => $totalBeneficiaries - $favoriteBeneficiaries,
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\StoreCodLocationRequest;
use App\Http\Resources\CodLocationResource;
use App\Models\CodLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Location
 * @tag Location - Manage shipping locations
 */
class CodLocationController extends Controller
{
    /**
     * Save a shipping location
     *
     * @authenticated
     * @requestBody required
     * @bodyParam name string required "Location name" example=Rumah
     * @bodyParam address string required "Full address" example=Jl. Contoh No. 123, Jakarta
     * @bodyParam latitude number "Latitude coordinate" example=-6.2088
     * @bodyParam longitude number "Longitude coordinate" example=106.8456
     * @bodyParam is_default boolean "Set as default location" example=true
     * @bodyParam phone string "Phone Number" example=0811223344
     * @response 201 body="{"success":true,"message":"Lokasi berhasil disimpan.","data":{}}"
     */
    public function store(StoreCodLocationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        // If this is set as default, unset other defaults for this user
        if (!empty($validated['is_default'])) {
            CodLocation::query()->where('user_id', '=', $user->id, 'and')
                ->update(['is_default' => false]);
        }

        $location = CodLocation::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'phone' => $validated['phone'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi pengiriman berhasil disimpan.',
            'data' => new CodLocationResource($location),
        ], 201);
    }

    /**
     * List the user's saved shipping locations
     *
     * @authenticated
     * @response 200 body="{"success":true,"data":[{}]}"
     */
    public function index(): JsonResponse
    {
        $locations = CodLocation::query()
            ->where('user_id', '=', Auth::id(), 'and')
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CodLocationResource::collection($locations),
        ]);
    }

    /**     * Set a shipping location as default
     *
     * @authenticated
     * @response 200 body="{\"success\":true,\"message\":\"Lokasi pengiriman default berhasil diperbarui.\",\"data\":{}}"
     * @response 403 body="{\"success\":false,\"message\":\"Akses ditolak.\"}"
     */
    public function setDefault(CodLocation $codLocation): JsonResponse
    {
        if ($codLocation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        CodLocation::query()->where('user_id', '=', Auth::id(), 'and')
            ->where('id', '!=', $codLocation->id)
            ->update(['is_default' => false]);

        $codLocation->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi pengiriman default berhasil diperbarui.',
            'data' => new CodLocationResource($codLocation),
        ]);
    }

    /**     * DELETE /api/buyer/locations/{id} - Delete a shipping location
     *
     * @authenticated
     * @response 200 body="{"success":true,"message":"Lokasi pengiriman berhasil dihapus."}"
     * @response 403 body="{"success":false,"message":"Akses ditolak."}"
     */
    public function destroy(CodLocation $codLocation): JsonResponse
    {
        if ($codLocation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        CodLocation::destroy($codLocation->id);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi pengiriman berhasil dihapus.',
        ]);
    }

    /**
     * PUT /api/buyer/locations/{id} - Update a shipping location
     *
     * @authenticated
     * @requestBody required
     * @bodyParam name string required "Location name" example=Rumah
     * @bodyParam address string required "Full address" example=Jl. Contoh No. 123, Jakarta
     * @bodyParam latitude number "Latitude coordinate" example=-6.2088
     * @bodyParam longitude number "Longitude coordinate" example=106.8456
     * @bodyParam is_default boolean "Set as default location" example=true
     * @response 200 body="{"success":true,"message":"Lokasi pengiriman berhasil diperbarui.","data":{}}"
     * @response 403 body="{"success":false,"message":"Akses ditolak."}"
     */
    public function update(StoreCodLocationRequest $request, CodLocation $codLocation): JsonResponse
    {
        if ($codLocation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $validated = $request->validated();

        // If this is set as default, unset other defaults for this user
        if (!empty($validated['is_default'])) {
            CodLocation::query()->where('user_id', '=', Auth::id(), 'and')
                ->where('id', '!=', $codLocation->id)
                ->update(['is_default' => false]);
        }

        $codLocation->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? $codLocation->latitude,
            'longitude' => $validated['longitude'] ?? $codLocation->longitude,
            'is_default' => $validated['is_default'] ?? $codLocation->is_default,
            'phone' => $validated['phone'] ?? $codLocation->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi pengiriman berhasil diperbarui.',
            'data' => new CodLocationResource($codLocation),
        ]);
    }
}

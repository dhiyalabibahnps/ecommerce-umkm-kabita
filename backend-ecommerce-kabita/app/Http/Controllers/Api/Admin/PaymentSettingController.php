<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentSettingResource;
use App\Models\PaymentSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Payment Setting
 * @tag Payment Setting - Admin payment settings management
 */
class PaymentSettingController extends Controller
{
    /**
     * Get current payment setting
     *
     * @authenticated
     * @response 200 body="{"success":true,"data":{"id":1,"bank_name":"BCA","account_number":"1234567890","account_holder_name":"PT Kabita","is_active":true}}"
     * @response 404 body="{"success":false,"message":"Pengaturan pembayaran belum diatur."}"
     */
    public function show(): JsonResponse
    {
        $setting = PaymentSetting::getActive();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan pembayaran belum diatur.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentSettingResource($setting),
        ]);
    }

    /**
     * Create payment setting
     *
     * @authenticated
     * @requestBody required
     * @bodyParam bank_name string required "Bank name" example=BCA
     * @bodyParam account_number string required "Account number" example=1234567890
     * @bodyParam account_holder_name string required "Account holder name" example=PT Kabita
     * @bodyParam is_active boolean "Active status" example=true
     * @response 201 body="{"success":true,"message":"Pengaturan pembayaran berhasil dibuat.","data":{}}"
     * @response 422 body="{"success":false,"message":"Pengaturan pembayaran sudah ada."}"
     */
    public function store(StorePaymentSettingRequest $request): JsonResponse
    {
        if (PaymentSetting::getActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan pembayaran sudah ada.',
            ], 422);
        }

        $setting = PaymentSetting::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan pembayaran berhasil dibuat.',
            'data' => new PaymentSettingResource($setting),
        ], 201);
    }

    /**
     * Update payment setting
     *
     * @authenticated
     * @requestBody required
     * @bodyParam bank_name string required "Bank name" example=BCA
     * @bodyParam account_number string required "Account number" example=1234567890
     * @bodyParam account_holder_name string required "Account holder name" example=PT Kabita
     * @bodyParam is_active boolean "Active status" example=true
     * @response 200 body="{"success":true,"message":"Pengaturan pembayaran berhasil diperbarui.","data":{}}"
     * @response 404 body="{"success":false,"message":"Pengaturan pembayaran belum diatur."}"
     */
    public function update(UpdatePaymentSettingRequest $request, PaymentSetting $paymentSetting): JsonResponse
    {
        $paymentSetting->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan pembayaran berhasil diperbarui.',
            'data' => new PaymentSettingResource($paymentSetting),
        ]);
    }
}

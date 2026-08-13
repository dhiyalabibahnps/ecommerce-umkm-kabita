<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentSettingResource;
use App\Models\PaymentSetting;
use Illuminate\Http\JsonResponse;

/**
 * @group Payment Setting
 * @tag Payment Setting - Platform payment settings for buyers
 */
class PaymentSettingController extends Controller
{
    /**
     * Get active payment setting
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
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\UploadPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

/**
 * @group Payment
 * @tag Payment - Payment operations (Buyer endpoints)
 */
class PaymentController extends Controller
{
  /**
   * Upload proof of payment transfer
   *
   * @authenticated
   * @requestBody required
   * @bodyParam proof_image file required "Payment proof image" example=proof.jpg
   * @response 200 body="{"success":true,"message":"Bukti pembayaran berhasil diunggah.","data":{}}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Pembayaran ini bukan milik Anda."}"
   */
  public function upload(UploadPaymentRequest $request, Payment $payment): JsonResponse
  {
    // Check if payment belongs to authenticated user's order
    if ($payment->order()->where('buyer_id', auth()->id())->doesntExist()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Pembayaran ini bukan milik Anda.',
      ], 403);
    }

    // Handle file upload
    if ($request->hasFile('proof_image')) {
      $path = $request->file('proof_image')->store('payments', 'public');
      $payment->update([
        'proof_image' => $path,
        'status' => \App\Enums\PaymentStatus::PENDING,
      ]);
    }

    // Load relationship for response
    $payment->load(['order.buyer', 'order.shop']);

    return response()->json([
      'success' => true,
      'message' => 'Bukti pembayaran berhasil diunggah.',
      'data' => new PaymentResource($payment),
    ]);
  }
}

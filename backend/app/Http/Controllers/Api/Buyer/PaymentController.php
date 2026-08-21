<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\UploadPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Enums\UserRole;
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
  public function upload(UploadPaymentRequest $request, int|string $payment): JsonResponse
  {
    $paymentModel = is_numeric($payment)
      ? (Payment::where('id', $payment)->first() ?? Payment::where('order_id', $payment)->first())
      : null;

    if (!$paymentModel) {
      return response()->json([
        'success' => false,
        'message' => 'Pembayaran tidak ditemukan.',
      ], 404);
    }

    // Check if payment belongs to authenticated user's order
    if ($paymentModel->order()->where('buyer_id', auth()->id())->doesntExist()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Pembayaran ini bukan milik Anda.',
      ], 403);
    }

    // Handle file upload
    if ($request->hasFile('proof_image')) {
      $path = $request->file('proof_image')->store('payments', 'public');
      $paymentModel->update([
        'proof_image' => $path,
        'status' => \App\Enums\PaymentStatus::PENDING,
      ]);

      // Load order info
      $order = $paymentModel->order;
      if ($order) {
        // Notification for Buyer
        Notification::create([
          'user_id' => auth()->id(),
          'type' => 'order',
          'title' => 'Bukti Pembayaran Terkirim',
          'message' => "Bukti pembayaran pesanan {$order->order_number} berhasil diunggah dan sedang diverifikasi admin.",
          'data' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'url' => "/order-detail?id={$order->id}",
          ],
          'is_read' => false,
        ]);

        // Notifications for Admins
        $adminUsers = User::where('role', UserRole::ADMIN)->get();
        foreach ($adminUsers as $admin) {
          Notification::create([
            'user_id' => $admin->id,
            'type' => 'order',
            'title' => 'Bukti Pembayaran Baru Menunggu Verifikasi',
            'message' => "Pesanan {$order->order_number} memiliki bukti transfer yang perlu diverifikasi.",
            'data' => [
              'order_id' => $order->id,
              'order_number' => $order->order_number,
              'payment_id' => $paymentModel->id,
              'url' => '/admin/verifikasi-produk',
            ],
            'is_read' => false,
          ]);
        }
      }
    }

    // Load relationship for response
    $paymentModel->load(['order.buyer', 'order.shop']);

    return response()->json([
      'success' => true,
      'message' => 'Bukti pembayaran berhasil diunggah.',
      'data' => new PaymentResource($paymentModel),
    ]);
  }
}

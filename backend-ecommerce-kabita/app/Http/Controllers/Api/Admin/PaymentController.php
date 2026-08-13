<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\RejectPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Payment
 * @tag Payment - Payment operations (Admin endpoints)
 */
class PaymentController extends Controller
{
  /**
   * List payments waiting for verification
   *
   * @authenticated
   * @query_param shop_id integer "Filter by shop ID"
   * @query_param buyer_id integer "Filter by buyer ID"
   * @query_param search string "Search by order number"
   * @query_param sort string "Sort: newest|oldest" default=newest
   * @query_param per_page integer "Items per page" default=15
   * @response 200 body="{"success":true,"data":[{}],"meta":{"current_page":1,"per_page":15,"total":50,"last_page":4}}"
   */
  public function index(Request $request): JsonResponse
  {
    $query = Payment::where('status', PaymentStatus::PENDING)
      ->with(['order.buyer', 'order.shop']);

    // Filter by shop_id
    if ($request->filled('shop_id')) {
      $query->whereHas('order.shop', function ($q) use ($request) {
        $q->where('id', $request->shop_id);
      });
    }

    // Filter by buyer_id
    if ($request->filled('buyer_id')) {
      $query->whereHas('order.buyer', function ($q) use ($request) {
        $q->where('id', $request->buyer_id);
      });
    }

    // Search by order number
    if ($request->filled('search')) {
      $query->whereHas('order', function ($q) use ($request) {
        $q->where('order_number', 'like', '%' . $request->search . '%');
      });
    }

    // Sorting
    match ($request->input('sort', 'newest')) {
      'oldest' => $query->orderBy('created_at', 'asc'),
      default => $query->orderBy('created_at', 'desc'),
    };

    $perPage = $request->input('per_page', 15);
    $payments = $query->paginate($perPage);

    return response()->json([
      'success' => true,
      'data' => PaymentResource::collection($payments),
      'meta' => [
        'current_page' => $payments->currentPage(),
        'per_page' => $payments->perPage(),
        'total' => $payments->total(),
        'last_page' => $payments->lastPage(),
      ],
    ]);
  }

  /**
   * Get single payment detail
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{}}"
   */
  public function show(Payment $payment): JsonResponse
  {
    $payment->load(['order.buyer', 'order.shop']);

    return response()->json([
      'success' => true,
      'data' => new PaymentResource($payment),
    ]);
  }

  /**
   * Verify a pending payment (Admin)
   *
   * @authenticated
   * @response 200 body="{"success":true,"message":"Pembayaran berhasil diverifikasi.","data":{}}"
   * @response 422 body="{"success":false,"message":"Pembayaran tidak dalam status pending."}"
   */
  public function verify(Payment $payment): JsonResponse
  {
    if ($payment->status !== PaymentStatus::PENDING) {
      return response()->json([
        'success' => false,
        'message' => 'Pembayaran tidak dalam status pending.',
      ], 422);
    }

    // Update payment status
    $payment->update(['status' => PaymentStatus::VERIFIED]);

    // Update order status to processing
    $order = $payment->order;
    $order->update(['status' => \App\Enums\OrderStatus::PROCESSING]);

    // Load relationships for response
    $order->load(['items', 'shop', 'payment', 'buyer']);

    // Notify seller
    $order->shop->seller->notify(new PaymentVerifiedMail(
      $order->buyer->name,
      $order->shop->seller->name,
      $order->order_number,
      number_format((float) $order->total_amount, 0, ',', '.')
    ));

    return response()->json([
      'success' => true,
      'message' => 'Pembayaran berhasil diverifikasi.',
      'data' => new OrderResource($order),
    ]);
  }

  /**
   * Reject a pending payment with reason (Admin)
   *
   * @authenticated
   * @requestBody required
   * @bodyParam rejection_reason string required "Reason for rejection" example=Pembayaran tidak sesuai
   * @response 200 body="{"success":true,"message":"Pembayaran berhasil ditolak.","data":{}}"
   * @response 422 body="{"success":false,"message":"Pembayaran tidak dalam status pending."}"
   */
  public function reject(Payment $payment, RejectPaymentRequest $request): JsonResponse
  {
    if ($payment->status !== PaymentStatus::PENDING) {
      return response()->json([
        'success' => false,
        'message' => 'Pembayaran tidak dalam status pending.',
      ], 422);
    }

    // Update payment status
    $payment->update(['status' => PaymentStatus::REJECTED]);

    // Load relationships for response
    $payment->load(['order.buyer', 'order.shop']);

    // Notify buyer
    $payment->order->buyer->notify(new PaymentRejectedMail(
      $payment->order->buyer->name,
      $payment->order->order_number,
      $request->input('rejection_reason', '')
    ));

    return response()->json([
      'success' => true,
      'message' => 'Pembayaran berhasil ditolak.',
      'data' => new PaymentResource($payment),
    ]);
  }
}

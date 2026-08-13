<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Requests\Order\ConfirmOrderRequest;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\DailyProductSales;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Order
 * @tag Order - Order management (Buyer endpoints)
 */
class OrderController extends Controller
{
  /**
   * List orders for the buyer
   *
   * @authenticated
   * @query_param status string "Filter by order status"
   * @query_param start_date string "Filter by start date (Y-m-d)"
   * @query_param end_date string "Filter by end date (Y-m-d)"
   * @query_param sort string "Sort: newest|oldest|total_asc|total_desc" default=newest
   * @query_param per_page integer "Items per page" default=15
   * @response 200 body="{"success":true,"data":[{}],"meta":{"current_page":1,"per_page":15,"total":50,"last_page":4}}"
   */
  public function index(IndexOrderRequest $request): JsonResponse
  {
    $query = Order::where('buyer_id', Auth::id())
      ->with(['items', 'shop', 'payment', 'buyer']);

    // Filter by status
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    // Filter by date range
    if ($request->filled('start_date')) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    // Sorting
    match ($request->input('sort', 'newest')) {
      'oldest' => $query->orderBy('created_at', 'asc'),
      'total_asc' => $query->orderBy('total_amount', 'asc'),
      'total_desc' => $query->orderBy('total_amount', 'desc'),
      default => $query->orderBy('created_at', 'desc'),
    };

    // Pagination
    $perPage = $request->input('per_page', 15);
    $orders = $query->paginate($perPage);

    return response()->json([
      'success' => true,
      'data' => OrderResource::collection($orders),
      'meta' => [
        'current_page' => $orders->currentPage(),
        'per_page' => $orders->perPage(),
        'total' => $orders->total(),
        'last_page' => $orders->lastPage(),
      ],
    ]);
  }

  /**
   * Get detailed order information for the buyer
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{}}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik Anda."}"
   */
  public function show(Order $order): JsonResponse
  {
    if ($order->buyer_id !== Auth::id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik Anda.',
      ], 403);
    }

    $order->load(['items', 'shop', 'payment', 'buyer']);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
    ]);
  }

  /**
   * Confirm goods received - marks order as delivered
   * Triggers update of daily_product_sales records.
   *
   * @authenticated
   * @requestBody required
   * @bodyParam confirmation_note string "Optional confirmation note" example=Barang sudah diterima dalam kondisi baik
   * @response 200 body="{"success":true,"data":{},"message":"Pesanan berhasil dikonfirmasi sebagai diterima."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status shipped yang dapat dikonfirmasi."}"
   */
  public function confirm(ConfirmOrderRequest $request, Order $order): JsonResponse
  {
    // Check if order belongs to buyer
    if ($order->buyer_id !== Auth::id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik Anda.',
      ], 403);
    }

    // Validate current status is shipped
    if ($order->status !== OrderStatus::SHIPPED) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status shipped yang dapat dikonfirmasi.',
      ], 422);
    }

    // Update order status to delivered
    $order->update(['status' => OrderStatus::DELIVERED]);

    // Update daily_product_sales for each item
    foreach ($order->items as $item) {
      DailyProductSales::updateOrCreate(
        [
          'date' => now()->toDateString(),
          'product_id' => $item->product_id,
          'shop_id' => $order->shop_id,
          'category_id' => $item->product->category_id,
        ],
        [
          'total_qty_sold' => DB::raw('total_qty_sold + ' . $item->quantity),
          'total_revenue' => DB::raw('total_revenue + ' . ($item->price_snapshot * $item->quantity)),
          'total_profit' => DB::raw('total_profit + ' . (($item->price_snapshot - $item->cost_snapshot) * $item->quantity)),
        ]
      );
    }

    $order->load(['items', 'shop', 'payment', 'buyer']);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Pesanan berhasil dikonfirmasi sebagai diterima.',
    ]);
  }

  /**
   * Cancel an order - transitions pending orders to cancelled
   * Validates status is pending, restores stock, and refunds if needed.
   *
   * @authenticated
   * @requestBody required
   * @bodyParam reason string required "Cancellation reason" example=Sudah menemukan barang di tempat lain
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil dibatalkan. Stok telah dikembalikan dan refund diproses."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status pending yang dapat dibatalkan."}"
   */
  public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
  {
    // Check if order belongs to buyer
    if ($order->buyer_id !== Auth::id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik Anda.',
      ], 403);
    }

    // Validate current status is pending
    if ($order->status !== OrderStatus::PENDING) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status pending yang dapat dibatalkan.',
      ], 422);
    }

    // Restore product stock
    foreach ($order->items as $item) {
      $item->product->increment('stock', $item->quantity);
    }

    // Process refund if payment was verified/paid
    if ($order->payment && in_array($order->payment->status, ['verified', 'paid'])) {
      $order->payment->update(['status' => 'refunded']);
      // TODO: Integrate with payment gateway for actual refund
    }

    // Update order status
    $order->update(['status' => OrderStatus::CANCELLED]);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil dibatalkan. Stok telah dikembalikan dan refund diproses.',
    ]);
  }

  /**
   * Confirm COD (Cash on Delivery) payment for an order
   *
   * @authenticated
   * @requestBody required
   * @bodyParam payment_proof_image file "Optional payment proof image" example=proof.jpg
   * @response 200 body="{"success":true,"data":{}}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan metode pembayaran COD yang dapat dikonfirmasi."}"
   */
  public function codConfirm(Order $order): JsonResponse
  {
    // Check if order belongs to buyer
    if ($order->buyer_id !== Auth::id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik Anda.',
      ], 403);
    }

    // Validate payment method is COD
    if ($order->payment_method !== 'cod') {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan metode pembayaran COD yang dapat dikonfirmasi.',
      ], 422);
    }

    // Load payment relationship
    $order->load('payment');

    // Validate payment exists and is pending
    if (!$order->payment || $order->payment->status !== PaymentStatus::PENDING) {
      return response()->json([
        'success' => false,
        'message' => 'Pembayaran tidak dalam status pending.',
      ], 422);
    }

    // Update payment status to verified
    $order->payment->update(['status' => PaymentStatus::VERIFIED]);

    // Update order status to processing
    $order->update(['status' => OrderStatus::PROCESSING]);

    // Load all relationships for response
    $order->load(['items', 'shop', 'payment', 'buyer']);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Pembayaran COD berhasil dikonfirmasi. Pesanan sedang diproses.',
    ]);
  }
}

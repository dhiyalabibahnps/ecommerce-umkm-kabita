<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\IndexOrderRequest;
use App\Http\Requests\Seller\ProcessOrderRequest;
use App\Http\Requests\Seller\ShipOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

/**
 * @group Order
 * @tag Order - Order management (Seller endpoints)
 */
class OrderController extends Controller
{
  /**
   * List orders for the seller's shop
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
    $query = Order::whereHas('shop', function ($query) {
      $query->where('seller_id', auth()->id());
    })->with(['items', 'shop', 'payment', 'buyer']);

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
   * Get detailed order information for the seller
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{}}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   */
  public function show(Order $order): JsonResponse
  {
    if ($order->shop->seller_id !== auth()->id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    $order->load(['items', 'shop', 'payment', 'buyer']);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
    ]);
  }

  /**
   * Process order from pending to processing
   * Validates that payment is verified before processing.
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil diproses."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status pending yang dapat diproses."}"
   */
  public function process(ProcessOrderRequest $request, Order $order): JsonResponse
  {
    // Check if order belongs to seller's shop
    if ($order->shop->seller_id !== auth()->id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    // Validate current status is pending
    if ($order->status !== OrderStatus::PENDING) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status pending yang dapat diproses.',
      ], 422);
    }

    // Validate payment is verified
    if (!$order->payment || $order->payment->status !== 'verified') {
      return response()->json([
        'success' => false,
        'message' => 'Pembayaran belum diverifikasi.',
      ], 422);
    }

    // Update order status
    $order->update(['status' => OrderStatus::PROCESSING]);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil diproses.',
    ]);
  }

  /**
   * Ship order from processing to shipped
   * Accepts optional tracking number for courier shipping.
   *
   * @authenticated
   * @requestBody required
   * @bodyParam tracking_number string "Tracking number for courier" example=JNE123456789
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil dikirim."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status processing yang dapat dikirim."}"
   */
  public function ship(ShipOrderRequest $request, Order $order): JsonResponse
  {
    // Check if order belongs to seller's shop
    if ($order->shop->seller_id !== auth()->id()) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    // Validate current status is processing
    if ($order->status !== OrderStatus::PROCESSING) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status processing yang dapat dikirim.',
      ], 422);
    }

    // Prepare update data
    $updateData = ['status' => OrderStatus::SHIPPED];

    // Add tracking number if provided
    if ($request->filled('tracking_number')) {
      $updateData['tracking_number'] = $request->tracking_number;
    }

    // Update order status
    $order->update($updateData);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil dikirim.',
    ]);
  }
}

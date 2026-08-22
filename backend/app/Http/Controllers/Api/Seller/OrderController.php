<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Seller\IndexOrderRequest;
use App\Http\Requests\Seller\PackOrderRequest;
use App\Http\Requests\Seller\ProcessOrderRequest;
use App\Http\Requests\Seller\ShipOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Notification;
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
    $sellerId = Auth::id(); // @intelephense-ignore
    $query = Order::forSeller($sellerId)->with(['items', 'shop', 'payment', 'buyer']);

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
    $sellerId = Auth::id(); // @intelephense-ignore
    if ($order->shop->seller_id !== $sellerId) {
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
   * Process order from processing (dikonfirmasi) to packed (dikemas)
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil diproses."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status dikonfirmasi yang dapat diproses."}"
   */
  public function process(ProcessOrderRequest $request, Order $order): JsonResponse
  {
    $sellerId = Auth::id(); // @intelephense-ignore
    if ($order->shop->seller_id !== $sellerId) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    if ($order->status !== OrderStatus::PROCESSING) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status processing yang dapat dikemas.',
      ], 422);
    }

    $order->update(['status' => OrderStatus::PACKED->value]);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil diproses ke status dikemas.',
    ]);
  }

  /**
   * Pack order from processing to packed
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil dikemas."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status processing yang dapat dikemas."}"
   */
  public function pack(PackOrderRequest $request, Order $order): JsonResponse
  {
    $sellerId = Auth::id(); // @intelephense-ignore
    if ($order->shop->seller_id !== $sellerId) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    if ($order->status !== OrderStatus::PROCESSING) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status processing yang dapat dikemas.',
      ], 422);
    }

    $order->update(['status' => OrderStatus::PACKED->value]);

    if ($order->buyer_id) {
      Notification::create([
        'user_id' => $order->buyer_id,
        'type' => 'order',
        'title' => 'Pesanan Sedang Dikemas',
        'message' => "Pesanan {$order->order_number} sedang dikemas oleh penjual dan disiapkan untuk pengiriman.",
        'data' => [
          'order_id' => $order->id,
          'order_number' => $order->order_number,
          'url' => "/order-detail?id={$order->id}",
        ],
        'is_read' => false,
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil dikemas.',
    ]);
  }

  /**
   * Ship order from packed to shipped
   * Requires tracking number for courier shipping.
   *
   * @authenticated
   * @requestBody required
   * @bodyParam tracking_number string required "Tracking number for courier" example=JNE123456789
   * @response 200 body="{"success":true,"data":{},"message":"Order berhasil dikirim."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order dengan status packed yang dapat dikirim."}"
   */
  public function ship(ShipOrderRequest $request, Order $order): JsonResponse
  {
    $sellerId = Auth::id(); // @intelephense-ignore
    if ($order->shop->seller_id !== $sellerId) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    if ($order->status !== OrderStatus::PACKED) {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order dengan status packed yang dapat dikirim.',
      ], 422);
    }

    if ($order->shipping_method === 'kurir' && !$request->filled('tracking_number')) {
      return response()->json([
        'success' => false,
        'message' => 'Nomor resi wajib diisi sebelum mengirim.',
      ], 422);
    }

    $courierName = $request->input('courier', $order->courier) ?? 'Kurir Ekspedisi';
    $trackingNum = $request->input('tracking_number');

    $order->update([
      'status' => OrderStatus::SHIPPED->value,
      'tracking_number' => $trackingNum,
      'courier' => $courierName,
    ]);

    if ($order->buyer_id) {
      $resiMsg = $trackingNum ? " via {$courierName} dengan no. resi {$trackingNum}" : "";
      Notification::create([
        'user_id' => $order->buyer_id,
        'type' => 'order',
        'title' => 'Pesanan Sedang Dikirim',
        'message' => "Pesanan {$order->order_number} telah dikirim{$resiMsg}. Lacak pengiriman Anda di halaman detail pesanan.",
        'data' => [
          'order_id' => $order->id,
          'order_number' => $order->order_number,
          'courier' => $courierName,
          'tracking_number' => $trackingNum,
          'url' => "/order-detail?id={$order->id}",
        ],
        'is_read' => false,
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Order berhasil dikirim.',
    ]);
  }

  /**
   * Confirm COD meeting from cod_meeting to completed
   *
   * @authenticated
   * @response 200 body="{"success":true,"data":{},"message":"Pesanan COD berhasil diselesaikan."}"
   * @response 403 body="{"success":false,"message":"Akses ditolak. Order ini bukan milik toko Anda."}"
   * @response 422 body="{"success":false,"message":"Hanya order COD dengan status ketemuan yang dapat diselesaikan."}"
   */
  public function codComplete(Order $order): JsonResponse
  {
    $sellerId = Auth::id(); // @intelephense-ignore
    if ($order->shop->seller_id !== $sellerId) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Order ini bukan milik toko Anda.',
      ], 403);
    }

    if ($order->status !== OrderStatus::COD_MEETING || $order->shipping_method !== 'cod') {
      return response()->json([
        'success' => false,
        'message' => 'Hanya order COD dengan status ketemuan yang dapat diselesaikan.',
      ], 422);
    }

    $order->update(['status' => OrderStatus::COMPLETED->value]);

    return response()->json([
      'success' => true,
      'data' => new OrderResource($order),
      'message' => 'Pesanan COD berhasil diselesaikan.',
    ]);
  }
}

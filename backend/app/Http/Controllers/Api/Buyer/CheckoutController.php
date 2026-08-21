<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Buyer;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CodLocation;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\CartCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @group Checkout
 * @tag Checkout - Checkout process
 */
class CheckoutController extends Controller
{
  public function __construct(
    private CartCalculationService $cartCalculationService,
  ) {}

  /**
   * Handle the checkout process.
   *
   * @authenticated
   * @requestBody required
   * @bodyParam cart_items array required "Array of cart item IDs" example=[1,2,3]
   * @bodyParam shipping_method string required "Shipping method: cod or kurir" example=cod
   * @bodyParam courier_type string "Courier type: reguler or express" example=reguler
   * @bodyParam payment_method string required "Payment method: transfer or cod" example=transfer
   * @bodyParam shipping_address string required "Shipping address" example=Jl. Contoh No. 123
   * @bodyParam notes string "Order notes" example=Please deliver before 5pm
   * @response 200 body="{"success":true,"message":"Checkout successful.","order":{}}"
   * @response 422 body="{"message":"Some items in your cart are out of stock or unavailable.","unavailable_items":[]}"
   */
  public function __invoke(CheckoutRequest $request): JsonResponse
  {
    $user = $request->user();
    $cart = Cart::where('buyer_id', $user->id)->with('items.product.shop')->firstOrFail();
    $cartItems = $this->getValidatedCartItems($request, $cart);

    // Validate stock availability
    $stockCheck = $this->cartCalculationService->checkStockAvailability($cart);
    if (!$stockCheck['available']) {
      return response()->json([
        'message' => 'Some items in your cart are out of stock or unavailable.',
        'unavailable_items' => $stockCheck['unavailable_items'],
      ], 422);
    }

    // Calculate totals
    $subtotal = $this->cartCalculationService->calculateSubtotal($cart);

    // Get cart weight for weight-based shipping calculation
    $cartWeight = $this->cartCalculationService->calculateCartWeight($cart);
    $courierType = $request->input('courier_type', 'reguler');
    $calculatedCost = $this->cartCalculationService->calculateShippingCostWithWeight(
      $request->input('shipping_method'),
      $cartWeight,
      $courierType
    );
    $shippingCost = $request->input('shipping_method') === 'cod'
      ? 0.0
      : ($request->filled('shipping_cost') ? (float) $request->input('shipping_cost') : $calculatedCost);
    $totalAmount = $subtotal + $shippingCost;

    // Group items by shop (assuming single shop for now)
    $groupedItems = $this->cartCalculationService->groupItemsByShop($cart);
    $shop = $groupedItems[0]['shop_id'] ?? null;

    if (!$shop) {
      return response()->json(['message' => 'No shop found for the cart items.'], 422);
    }

    // Generate order number
    $orderNumber = $this->generateOrderNumber();

    // Resolve shipping address from saved location if provided
    $shippingAddress = $request->input('shipping_address');
    $locationId = $request->input('location_id');

    if ($locationId) {
      $location = CodLocation::where('user_id', $user->id)->findOrFail($locationId);
      $shippingAddress = $location->address;
    }

    // Create order
    $order = Order::create([
      'order_number' => $orderNumber,
      'buyer_id' => $user->id,
      'shop_id' => $shop,
      'subtotal' => $subtotal,
      'shipping_cost' => $shippingCost,
      'total_amount' => $totalAmount,
      'shipping_method' => $request->input('shipping_method'),
      'courier' => $request->input('courier'),
      'payment_method' => $request->input('payment_method'),
      'status' => OrderStatus::AWAITING_VERIFICATION,
      'shipping_address' => $shippingAddress,
      'notes' => $request->input('notes'),
    ]);

    // Create order items and reduce product stock
    $this->createOrderItems($order, $cartItems);

    // Remove items from cart
    $cartItems->each->delete();

    // Create payment record
    $payment = Payment::create([
      'order_id' => $order->id,
      'amount' => $totalAmount,
      'status' => PaymentStatus::PENDING,
    ]);

    // Create notification for seller
    $order->load(['shop.seller', 'items.product']);
    $sellerId = $order->shop?->seller_id;
    if ($sellerId) {
      $itemNames = $order->items->map(fn($item) => $item->product?->name)->filter()->take(2)->join(', ');
      $itemCount = $order->items->count();
      $summaryText = $itemCount > 2 ? "{$itemNames} dan lainnya ({$itemCount} produk)" : "{$itemNames} ({$itemCount} produk)";

      Notification::create([
        'user_id' => $sellerId,
        'type' => 'order',
        'title' => 'Pesanan Baru Masuk',
        'message' => "Pesanan {$order->order_number} dari {$user->name}: {$summaryText} - Total Rp " . number_format($totalAmount, 0, ',', '.'),
        'data' => [
          'order_id' => $order->id,
          'order_number' => $order->order_number,
          'buyer_name' => $user->name,
          'total_amount' => $totalAmount,
          'url' => "/seller/pesanan/{$order->id}",
        ],
        'is_read' => false,
      ]);
    }

    // Create notification for buyer
    Notification::create([
      'user_id' => $user->id,
      'type' => 'order',
      'title' => 'Pesanan Berhasil Dibuat',
      'message' => "Pesanan {$order->order_number} berhasil dibuat. Total pembayaran Rp " . number_format($totalAmount, 0, ',', '.') . ".",
      'data' => [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'total_amount' => $totalAmount,
        'url' => "/order-detail?id={$order->id}",
      ],
      'is_read' => false,
    ]);

    // Load relationships for the response
    $order->load(['buyer', 'shop', 'items.product', 'payment']);

    return response()->json([
      'message' => 'Checkout successful.',
      'order' => new OrderResource($order),
    ]);
  }

  /**
   * Get the validated cart items for the checkout.
   */
  private function getValidatedCartItems(CheckoutRequest $request, Cart $cart): Collection
  {
    $cartItemIds = $request->input('cart_items');
    return CartItem::whereIn('id', $cartItemIds)->where('cart_id', $cart->id)->with('product')->get();
  }

  /**
   * Generate a unique order number.
   */
  private function generateOrderNumber(): string
  {
    $date = Carbon::now()->format('Ymd');
    $random = Str::upper(Str::random(5));
    return "ORD-{$date}-{$random}";
  }

  /**
   * Create order items and reduce product stock.
   */
  private function createOrderItems(Order $order, Collection $cartItems): void
  {
    $cartItems->each(function (CartItem $cartItem) use ($order) {
      OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $cartItem->product_id,
        'quantity' => $cartItem->quantity,
        'price_snapshot' => $cartItem->product->price,
        'cost_snapshot' => $cartItem->product->cost_price,
      ]);

      // Reduce product stock
      $product = Product::find($cartItem->product_id);
      $product->decrement('stock', $cartItem->quantity);
    });
  }
}

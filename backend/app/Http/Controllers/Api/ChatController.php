<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @group Chat
 * @tag Chat - Two-way messaging between Buyer and Seller
 */
class ChatController extends Controller
{
    /**
     * Get conversation by ID and fetch its messages.
     */
    public function getConversation(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if ($user->id !== $conversation->buyer_id && $user->id !== $conversation->seller_id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki akses ke percakapan ini.',
            ], 403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->load(['buyer', 'seller', 'shop', 'order', 'messages.sender']);

        return response()->json([
            'success' => true,
            'data' => new ConversationResource($conversation),
        ]);
    }

    /**
     * Send a message to an existing conversation by conversation ID.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        if ($user->id !== $conversation->buyer_id && $user->id !== $conversation->seller_id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak dapat mengirim pesan di percakapan ini.',
            ], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $recipientId = ($user->id === $conversation->buyer_id) ? $conversation->seller_id : $conversation->buyer_id;
        $order = $conversation->order;
        $url = $order
            ? (($recipientId === $conversation->seller_id) ? "/seller/pesanan/{$order->id}" : "/order-detail?id={$order->id}")
            : (($recipientId === $conversation->seller_id) ? "/seller/dashboard" : "/");

        Notification::create([
            'user_id' => $recipientId,
            'type' => 'chat',
            'title' => 'Pesan Baru dari ' . $user->name,
            'message' => Str::limit($request->input('message'), 80),
            'data' => [
                'conversation_id' => $conversation->id,
                'order_id' => $conversation->order_id,
                'order_number' => $order?->order_number,
                'shop_id' => $conversation->shop_id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'url' => $url,
            ],
            'is_read' => false,
        ]);

        $message->load('sender');

        return response()->json([
            'success' => true,
            'data' => new MessageResource($message),
            'message' => 'Pesan berhasil dikirim.',
        ], 201);
    }

    /**
     * Get or create conversation for an order and fetch its messages.
     */
    public function getOrderConversation(Order $order): JsonResponse
    {
        $user = Auth::user();
        $order->load(['shop.seller', 'buyer']);

        $sellerId = $order->shop->seller_id;
        $buyerId = $order->buyer_id;

        if ($user->id !== $buyerId && $user->id !== $sellerId && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki akses ke percakapan ini.',
            ], 403);
        }

        $conversation = Conversation::firstOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'shop_id' => $order->shop_id,
                'last_message_at' => now(),
            ]
        );

        // Mark unread messages sent by the other party as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->load(['buyer', 'seller', 'shop', 'order', 'messages.sender']);

        return response()->json([
            'success' => true,
            'data' => new ConversationResource($conversation),
        ]);
    }

    /**
     * Send a message in an order's conversation.
     */
    public function sendOrderMessage(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $order->load(['shop.seller', 'buyer']);

        $sellerId = $order->shop->seller_id;
        $buyerId = $order->buyer_id;

        if ($user->id !== $buyerId && $user->id !== $sellerId && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak dapat mengirim pesan di pesanan ini.',
            ], 403);
        }

        $conversation = Conversation::firstOrCreate(
            [
                'order_id' => $order->id,
            ],
            [
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'shop_id' => $order->shop_id,
            ]
        );

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $recipientId = ($user->id === $buyerId) ? $sellerId : $buyerId;
        Notification::create([
            'user_id' => $recipientId,
            'type' => 'chat',
            'title' => 'Pesan Baru dari ' . $user->name,
            'message' => Str::limit($request->input('message'), 80),
            'data' => [
                'conversation_id' => $conversation->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'shop_id' => $order->shop_id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'url' => ($recipientId === $sellerId) ? "/seller/pesanan/{$order->id}" : "/order-detail?id={$order->id}",
            ],
            'is_read' => false,
        ]);

        $message->load('sender');

        return response()->json([
            'success' => true,
            'data' => new MessageResource($message),
            'message' => 'Pesan berhasil dikirim.',
        ], 201);
    }

    /**
     * Get or create general conversation with a shop/seller (e.g. from product detail).
     */
    public function getShopConversation(Shop $shop): JsonResponse
    {
        $user = Auth::user();
        $shop->load('seller');

        $sellerId = $shop->seller_id;
        $buyerId = $user->id;

        $conversation = Conversation::firstOrCreate(
            [
                'shop_id' => $shop->id,
                'buyer_id' => $buyerId,
                'order_id' => null,
            ],
            [
                'seller_id' => $sellerId,
                'last_message_at' => now(),
            ]
        );

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->load(['buyer', 'seller', 'shop', 'messages.sender']);

        return response()->json([
            'success' => true,
            'data' => new ConversationResource($conversation),
        ]);
    }

    /**
     * Send message in shop/product conversation.
     */
    public function sendShopMessage(Request $request, Shop $shop): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'product_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $shop->load('seller');

        $sellerId = $shop->seller_id;
        $buyerId = $user->id;

        $conversation = Conversation::firstOrCreate(
            [
                'shop_id' => $shop->id,
                'buyer_id' => $buyerId,
                'order_id' => null,
            ],
            [
                'seller_id' => $sellerId,
            ]
        );

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        $productName = $request->input('product_name');
        $title = $productName
            ? "Tanya Produk ({$productName}) dari {$user->name}"
            : "Pesan Baru dari {$user->name}";

        Notification::create([
            'user_id' => $sellerId,
            'type' => 'chat',
            'title' => $title,
            'message' => Str::limit($request->input('message'), 80),
            'data' => [
                'conversation_id' => $conversation->id,
                'shop_id' => $shop->id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'product_name' => $productName,
                'url' => '/seller/dashboard',
            ],
            'is_read' => false,
        ]);

        $message->load('sender');

        return response()->json([
            'success' => true,
            'data' => new MessageResource($message),
            'message' => 'Pesan berhasil dikirim.',
        ], 201);
    }
}

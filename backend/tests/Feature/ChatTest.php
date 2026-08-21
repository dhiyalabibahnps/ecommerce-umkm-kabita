<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Shop $shop;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->shop = Shop::factory()->create(['seller_id' => $this->seller->id]);

        $this->order = Order::factory()->create([
            'buyer_id' => $this->buyer->id,
            'shop_id' => $this->shop->id,
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    public function test_buyer_can_get_order_conversation(): void
    {
        $response = $this->actingAs($this->buyer)
            ->getJson("/api/v1/chat/orders/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_id', $this->order->id)
            ->assertJsonPath('data.buyer_id', $this->buyer->id)
            ->assertJsonPath('data.seller_id', $this->seller->id);
    }

    public function test_seller_can_get_order_conversation(): void
    {
        $response = $this->actingAs($this->seller)
            ->getJson("/api/v1/chat/orders/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_id', $this->order->id);
    }

    public function test_unrelated_user_cannot_access_order_conversation(): void
    {
        $unrelatedUser = User::factory()->create(['role' => 'buyer', 'status' => 'active']);

        $response = $this->actingAs($unrelatedUser)
            ->getJson("/api/v1/chat/orders/{$this->order->id}");

        $response->assertStatus(403);
    }

    public function test_buyer_and_seller_share_the_same_conversation(): void
    {
        // Buyer sends message
        $sendResponse1 = $this->actingAs($this->buyer)
            ->postJson("/api/v1/chat/orders/{$this->order->id}/messages", [
                'message' => 'Halo seller, paket saya tolong dipacking aman ya.',
            ]);

        $sendResponse1->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Halo seller, paket saya tolong dipacking aman ya.');

        // Seller opens chat and sees the message
        $sellerViewResponse = $this->actingAs($this->seller)
            ->getJson("/api/v1/chat/orders/{$this->order->id}");

        $sellerViewResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.messages')
            ->assertJsonPath('data.messages.0.message', 'Halo seller, paket saya tolong dipacking aman ya.');

        // Seller replies
        $sendResponse2 = $this->actingAs($this->seller)
            ->postJson("/api/v1/chat/orders/{$this->order->id}/messages", [
                'message' => 'Siap kak, akan kami packing dengan bubble wrap tebal!',
            ]);

        $sendResponse2->assertStatus(201);

        // Buyer opens chat and sees both messages
        $buyerViewResponse = $this->actingAs($this->buyer)
            ->getJson("/api/v1/chat/orders/{$this->order->id}");

        $buyerViewResponse->assertStatus(200)
            ->assertJsonCount(2, 'data.messages');
    }

    public function test_user_can_access_and_send_messages_by_conversation_id(): void
    {
        $conversation = Conversation::create([
            'order_id' => $this->order->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'shop_id' => $this->shop->id,
            'last_message_at' => now(),
        ]);

        // Buyer gets conversation by ID
        $getResponse = $this->actingAs($this->buyer)
            ->getJson("/api/v1/chat/conversations/{$conversation->id}");

        $getResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $conversation->id);

        // Buyer sends message by conversation ID
        $sendResponse = $this->actingAs($this->buyer)
            ->postJson("/api/v1/chat/conversations/{$conversation->id}/messages", [
                'message' => 'Halo dari notifikasi langsung!',
            ]);

        $sendResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Halo dari notifikasi langsung!');

        // Seller receives notification with metadata
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->seller->id,
            'type' => 'chat',
        ]);

        // Seller can also read conversation by ID
        $sellerGetResponse = $this->actingAs($this->seller)
            ->getJson("/api/v1/chat/conversations/{$conversation->id}");

        $sellerGetResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.messages');
    }

    public function test_unrelated_user_cannot_access_conversation_by_id(): void
    {
        $conversation = Conversation::create([
            'order_id' => $this->order->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'shop_id' => $this->shop->id,
            'last_message_at' => now(),
        ]);

        $unrelatedUser = User::factory()->create(['role' => 'buyer', 'status' => 'active']);

        $response = $this->actingAs($unrelatedUser)
            ->getJson("/api/v1/chat/conversations/{$conversation->id}");

        $response->assertStatus(403);
    }
}

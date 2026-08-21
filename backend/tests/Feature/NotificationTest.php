<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Shop $shop;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'name' => 'Buyer Test']);
        $this->seller = User::factory()->create(['role' => 'seller', 'status' => 'active', 'name' => 'Seller Test']);
        $this->shop = Shop::factory()->create(['seller_id' => $this->seller->id, 'name' => 'Toko Mantap']);

        $this->order = Order::factory()->create([
            'buyer_id' => $this->buyer->id,
            'shop_id' => $this->shop->id,
            'status' => OrderStatus::PROCESSING,
        ]);
    }

    public function test_user_can_get_notifications_and_unread_count(): void
    {
        Notification::create([
            'user_id' => $this->seller->id,
            'type' => 'order',
            'title' => 'Pesanan Baru Masuk',
            'message' => 'Pesanan ORD-12345 diterima.',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $this->seller->id,
            'type' => 'chat',
            'title' => 'Pesan Baru dari Buyer',
            'message' => 'Halo apakah barang ready?',
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->seller)
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $notif = Notification::create([
            'user_id' => $this->seller->id,
            'type' => 'chat',
            'title' => 'Pesan Baru',
            'message' => 'Test',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->seller)
            ->putJson("/api/v1/notifications/{$notif->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertTrue($notif->fresh()->is_read);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        Notification::create([
            'user_id' => $this->seller->id,
            'type' => 'order',
            'title' => 'Order 1',
            'message' => 'Msg 1',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $this->seller->id,
            'type' => 'order',
            'title' => 'Order 2',
            'message' => 'Msg 2',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->seller)
            ->putJson('/api/v1/notifications/read-all');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertEquals(0, Notification::where('user_id', $this->seller->id)->where('is_read', false)->count());
    }

    public function test_sending_chat_message_creates_notification_for_recipient(): void
    {
        // Buyer sends chat to seller
        $this->actingAs($this->buyer)
            ->postJson("/api/v1/chat/orders/{$this->order->id}/messages", [
                'message' => 'Tolong kirim secepatnya ya seller.',
            ]);

        // Seller should have 1 unread notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->seller->id,
            'type' => 'chat',
            'is_read' => false,
        ]);

        $sellerNotif = Notification::where('user_id', $this->seller->id)->first();
        $this->assertStringContainsString('Buyer Test', $sellerNotif->title);
        $this->assertEquals('Tolong kirim secepatnya ya seller.', $sellerNotif->message);
    }
}

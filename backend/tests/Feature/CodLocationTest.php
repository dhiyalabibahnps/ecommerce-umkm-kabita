<?php

namespace Tests\Feature;

use App\Models\CodLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodLocationTest extends TestCase
{
  use RefreshDatabase;

  private User $buyer;

  protected function setUp(): void
  {
    parent::setUp();
    $this->buyer = User::factory()->create(['role' => 'buyer']);
  }

  // ==========================================
  // POST /api/locations - Store Location
  // ==========================================

  public function test_can_store_location(): void
  {
    $locationData = [
      'name' => 'Rumah',
      'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
      'latitude' => -6.2088,
      'longitude' => 106.8456,
      'is_default' => true,
    ];

    $response = $this->actingAs($this->buyer)
      ->postJson('/api/v1/locations', $locationData);

    $response->assertStatus(201)
      ->assertJson([
        'success' => true,
        'message' => 'Lokasi pengiriman berhasil disimpan.',
      ]);

    $this->assertDatabaseHas('cod_locations', [
      'user_id' => $this->buyer->id,
      'name' => 'Rumah',
      'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $location = CodLocation::where('user_id', $this->buyer->id)->first();
    $this->assertTrue($location->is_default);
  }

  public function test_cannot_store_without_required_fields(): void
  {
    $response = $this->actingAs($this->buyer)
      ->postJson('/api/v1/locations', []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'address']);
  }

  public function test_longitude_must_be_between_minus_180_and_180(): void
  {
    $response = $this->actingAs($this->buyer)
      ->postJson('/api/v1/locations', [
        'name' => 'Kantor',
        'address' => 'Jl. Thamrin No. 45, Jakarta Pusat',
        'longitude' => 200,
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['longitude']);
  }

  public function test_latitude_must_be_between_minus_90_and_90(): void
  {
    $response = $this->actingAs($this->buyer)
      ->postJson('/api/v1/locations', [
        'name' => 'Sekolah',
        'address' => 'Jl. Gatot Subroto No. 78, Bandung',
        'latitude' => 100,
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['latitude']);
  }

  public function test_setting_new_default_unsets_old_defaults(): void
  {
    $firstLocation = CodLocation::factory()->create([
      'user_id' => $this->buyer->id,
      'is_default' => true,
    ]);

    $secondLocation = CodLocation::factory()->create([
      'user_id' => $this->buyer->id,
      'is_default' => false,
    ]);

    $response = $this->actingAs($this->buyer)
      ->postJson('/api/v1/locations', [
        'name' => 'Tempat Baru',
        'address' => 'Jl. Asia Afrika No. 10, Bandung',
        'is_default' => true,
      ]);

    $response->assertStatus(201);

    // Verify first location is no longer default
    $firstLocation->refresh();
    $this->assertFalse($firstLocation->is_default);

    // Verify new location is default
    $newLocation = CodLocation::where('name', 'Tempat Baru')->first();
    $this->assertTrue($newLocation->is_default);
  }

  // ==========================================
  // GET /api/locations - List Locations
  // ==========================================

  public function test_can_list_user_locations(): void
  {
    CodLocation::factory()->count(3)->create([
      'user_id' => $this->buyer->id,
    ]);

    CodLocation::factory()->create([
      'user_id' => User::factory()->create(['role' => 'buyer'])->id,
    ]);

    $response = $this->actingAs($this->buyer)
      ->getJson('/api/v1/locations');

    $response->assertStatus(200)
      ->assertJsonCount(3, 'data');
  }

  public function test_list_returns_empty_array_when_no_locations(): void
  {
    $response = $this->actingAs($this->buyer)
      ->getJson('/api/v1/locations');

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'data' => [],
      ]);
  }

  // ==========================================
  // PUT /api/locations/{id} - Update Location
  // ==========================================

  public function test_can_update_location(): void
  {
    $location = CodLocation::factory()->create([
      'user_id' => $this->buyer->id,
    ]);

    $updateData = [
      'name' => 'Rumah Baru',
      'address' => 'Jl. Merdeka No. 45, Surabaya',
    ];

    $response = $this->actingAs($this->buyer)
      ->putJson("/api/v1/locations/{$location->id}", $updateData);

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'message' => 'Lokasi pengiriman berhasil diperbarui.',
      ]);

    $location->refresh();
    $this->assertEquals('Rumah Baru', $location->name);
    $this->assertEquals('Jl. Merdeka No. 45, Surabaya', $location->address);
  }

  public function test_cannot_update_another_users_shipping_location(): void
  {
    $otherUser = User::factory()->create(['role' => 'buyer']);
    $location = CodLocation::factory()->create([
      'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->buyer)
      ->putJson("/api/v1/locations/{$location->id}", [
        'name' => 'Malicious Update',
        'address' => 'Jl. Test No. 1, Jakarta',
      ]);

    $response->assertStatus(403);
  }

  public function test_update_validates_required_fields(): void
  {
    $location = CodLocation::factory()->create([
      'user_id' => $this->buyer->id,
    ]);

    $response = $this->actingAs($this->buyer)
      ->putJson("/api/v1/locations/{$location->id}", []);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['name', 'address']);
  }

  // ==========================================
  // DELETE /api/locations/{id} - Delete Location
  // ==========================================

  public function test_can_delete_own_location(): void
  {
    $location = CodLocation::factory()->create([
      'user_id' => $this->buyer->id,
    ]);

    $response = $this->actingAs($this->buyer)
      ->deleteJson("/api/v1/locations/{$location->id}");

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'message' => 'Lokasi pengiriman berhasil dihapus.',
      ]);

    $this->assertDatabaseMissing('cod_locations', [
      'id' => $location->id,
    ]);
  }

  public function test_cannot_delete_another_users_shipping_location(): void
  {
    $otherUser = User::factory()->create(['role' => 'buyer']);
    $location = CodLocation::factory()->create([
      'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($this->buyer)
      ->deleteJson("/api/v1/locations/{$location->id}");

    $response->assertStatus(403);
  }

  public function test_unauthenticated_user_cannot_access_locations(): void
  {
    $response = $this->getJson('/api/v1/locations');
    $response->assertStatus(401);

    $response = $this->postJson('/api/v1/locations', [
      'name' => 'Test',
      'address' => 'Test Address',
    ]);
    $response->assertStatus(401);
  }
}

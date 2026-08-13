<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // Password default: 'password'
            'role' => fake()->randomElement(UserRole::cases()), // Acak antara Admin, Seller, Buyer
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => UserStatus::ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State khusus untuk Seller yang belum diverifikasi
     */
    public function pendingSeller(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::SELLER,
            'proof_image' => 'dummy_proof_' . uniqid() . '.jpg',
            'status' => UserStatus::INACTIVE,
            'verified_at' => null,
        ]);
    }

    /**
     * State khusus untuk Admin
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN,
            'name' => 'Administrator Kabita',
            'email' => 'admin@kabita.test',
        ]);
    }
}

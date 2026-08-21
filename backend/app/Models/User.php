<?php

namespace App\Models;

use Database\Factories\UserFactory;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read Shop|null $shop
 * @property-read User|null $verifiedBy
 * @property-read Cart|null $cart
 * @property-read \Illuminate\Database\Eloquent\Collection<Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<CodLocation> $codLocations
 */

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // Tambahan: admin, seller, buyer
        'phone',     // Tambahan: nomor WhatsApp
        'address',   // Tambahan: alamat user
        'photo',     // Tambahan: foto profil
        'status',    // Tambahan: active, inactive, suspended
        'proof_image',
        'verified_by',
        'verified_at',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    // ==========================================
    // RELATIONSHIPS (SESUAI RELASI DATABASE)
    // ==========================================

    /**
     * Get the shop associated with the user (Untuk Seller).
     */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'seller_id');
    }

    /**
     * Get the user who verified this user.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the cart associated with the user (Untuk Buyer).
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'buyer_id');
    }

    /**
     * Get the orders for the user (Untuk Buyer).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the email verification codes for the user.
     */
    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    /**
     * Get the COD locations for the user.
     */
    public function codLocations(): HasMany
    {
        return $this->hasMany(CodLocation::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}

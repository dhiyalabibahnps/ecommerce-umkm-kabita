<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $bank_name
 * @property string $account_number
 * @property string $account_holder_name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class PaymentSetting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_holder_name',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the active payment setting.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}

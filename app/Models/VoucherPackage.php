<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherPackage extends Model
{
    protected $fillable = ['name', 'applies_to', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(VoucherPackageItem::class);
    }

    public function itemsWithType()
    {
        return $this->items()->join('voucher_types', 'voucher_types.id', '=', 'voucher_package_items.voucher_type_id')
            ->orderBy('voucher_types.sort_order')
            ->select('voucher_package_items.*');
    }

    public function totalQty(): int
    {
        return (int) $this->items()->sum('qty');
    }

    /** Paket default sesuai jenis keanggotaan (paid/free). */
    public static function defaultFor(string $membershipType): ?self
    {
        return static::where('is_active', true)
            ->where('is_default', true)
            ->where(function ($q) use ($membershipType) {
                $q->where('applies_to', 'both')->orWhere('applies_to', $membershipType);
            })
            ->first();
    }
}

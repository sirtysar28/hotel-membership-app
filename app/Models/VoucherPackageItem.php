<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherPackageItem extends Model
{
    protected $fillable = ['voucher_package_id', 'voucher_type_id', 'qty'];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function package()
    {
        return $this->belongsTo(VoucherPackage::class, 'voucher_package_id');
    }

    public function type()
    {
        return $this->belongsTo(VoucherType::class, 'voucher_type_id');
    }
}

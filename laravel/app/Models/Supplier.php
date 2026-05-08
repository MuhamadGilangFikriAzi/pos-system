<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'code', 'contact_person', 'phone',
        'email', 'address', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $s) {
            if (empty($s->code)) {
                $max = static::max('id') ?? 0;
                $s->code = 'SUP-' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}

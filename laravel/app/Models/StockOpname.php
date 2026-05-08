<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number', 'warehouse_id', 'user_id',
        'status', 'notes', 'opname_date',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $op) {
            if (empty($op->opname_number)) {
                $op->opname_number = 'OP-' . now()->format('Ymd') . '-' . str_pad((string) (static::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}

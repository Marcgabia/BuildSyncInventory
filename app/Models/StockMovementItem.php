<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One SKU line of a stock movement. quantity is always positive. */
class StockMovementItem extends Model
{
    protected $table = 'stock_movement_items';

    protected $fillable = ['stock_movement_id', 'sku', 'quantity', 'quantity_before', 'quantity_after'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'quantity_before' => 'integer', 'quantity_after' => 'integer'];
    }

    public $timestamps = false;

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }
}

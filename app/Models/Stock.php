<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Quantity of one SKU in one warehouse.
 * Never change quantity directly: always go through StockMovementService (A2).
 */
class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = ['sku', 'warehouse_code', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_code', 'warehouse_code');
    }
}

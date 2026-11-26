<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** INV owns products (B4). sku is the code shared with other systems. */
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['sku', 'name', 'category', 'unit', 'price', 'cost', 'reorder_level', 'reorder_quantity', 'preferred_supplier_code', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'cost' => 'decimal:2', 'reorder_level' => 'integer', 'reorder_quantity' => 'integer'];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'sku', 'sku');
    }
}

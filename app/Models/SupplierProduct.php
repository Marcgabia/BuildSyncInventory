<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only copy owned by another system (A3). Changed only by the seeder and integration:sync-masterdata. */
class SupplierProduct extends Model
{
    protected $table = 'supplier_products';

    protected $fillable = ['supplier_code', 'sku', 'unit_cost', 'lead_time_days', 'is_preferred', 'source_updated_at'];

    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2', 'lead_time_days' => 'integer', 'is_preferred' => 'boolean', 'source_updated_at' => 'datetime'];
    }
}

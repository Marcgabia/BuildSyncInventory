<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** F4: stock fell to or below reorder_level. Only one Open alert per SKU + warehouse (enforced by the database). */
class LowStockAlert extends Model
{
    protected $table = 'low_stock_alerts';

    protected $fillable = ['alert_number', 'sku', 'warehouse_code', 'branch_code', 'quantity_at_alert', 'reorder_level', 'status', 'pr_number', 'closed_at'];

    protected function casts(): array
    {
        return ['quantity_at_alert' => 'integer', 'reorder_level' => 'integer', 'closed_at' => 'datetime'];
    }
}

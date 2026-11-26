<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One posted change of stock (SM-YYYYMMDD-0001) with its items (A3). */
class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = ['movement_number', 'movement_type', 'source_system', 'reference_number', 'branch_code', 'warehouse_code', 'performed_by', 'remarks', 'status'];

    protected function casts(): array
    {
        return [];
    }

    /** Movement types that add stock; every other type removes stock. */
    public const IN_TYPES = ['SALE_RETURN', 'PURCHASE_IN', 'OPENING_BALANCE', 'ADJUSTMENT_IN', 'TRANSFER_IN'];

    public const OUT_TYPES = ['SALE_OUT', 'SUPPLIER_RETURN_OUT', 'ADJUSTMENT_OUT', 'TRANSFER_OUT'];

    /** Which movement types each source system may send through the API (A3). */
    public const TYPES_BY_SOURCE = [
        'CRM' => ['SALE_OUT', 'SALE_RETURN'],
        'PROC' => ['PURCHASE_IN', 'SUPPLIER_RETURN_OUT'],
        'INV' => ['OPENING_BALANCE', 'ADJUSTMENT_IN', 'ADJUSTMENT_OUT', 'TRANSFER_OUT', 'TRANSFER_IN'],
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function isIn(): bool
    {
        return in_array($this->movement_type, self::IN_TYPES, true);
    }
}

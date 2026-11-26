<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** INV owns warehouses (B4). Only one default warehouse per branch (enforced by the database). */
class Warehouse extends Model
{
    protected $table = 'warehouses';

    protected $fillable = ['warehouse_code', 'name', 'branch_code', 'is_default', 'status'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'warehouse_code', 'warehouse_code');
    }
}

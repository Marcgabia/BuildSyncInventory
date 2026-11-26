<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only copy owned by another system (A3). Changed only by the seeder and integration:sync-masterdata. */
class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = ['supplier_code', 'name', 'contact_person', 'email', 'phone', 'status', 'source_updated_at'];

    protected function casts(): array
    {
        return ['source_updated_at' => 'datetime'];
    }
}

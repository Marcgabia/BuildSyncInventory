<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only copy owned by another system (A3). Changed only by the seeder and integration:sync-masterdata. */
class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = ['branch_code', 'name', 'city', 'status', 'source_updated_at'];

    protected function casts(): array
    {
        return ['source_updated_at' => 'datetime'];
    }
}

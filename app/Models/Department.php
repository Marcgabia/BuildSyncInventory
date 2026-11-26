<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only copy owned by another system (A3). Changed only by the seeder and integration:sync-masterdata. */
class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = ['department_code', 'name', 'status', 'source_updated_at'];

    protected function casts(): array
    {
        return ['source_updated_at' => 'datetime'];
    }
}

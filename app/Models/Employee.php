<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Read-only copy owned by another system (A3). Changed only by the seeder and integration:sync-masterdata. */
class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = ['employee_no', 'first_name', 'last_name', 'email', 'branch_code', 'department_code', 'position', 'employment_status', 'can_login', 'source_updated_at'];

    protected function casts(): array
    {
        return ['can_login' => 'boolean', 'source_updated_at' => 'datetime'];
    }
}

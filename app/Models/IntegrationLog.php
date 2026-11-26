<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * B7: one row per integration call, incoming ("in") or outgoing ("out").
 */
class IntegrationLog extends Model
{
    // The table only has created_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'direction',
        'system',
        'method',
        'url',
        'reference_number',
        'request_body',
        'status_code',
        'response_body',
        'duration_ms',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'request_body' => 'array',
            'response_body' => 'array',
            'created_at' => 'datetime',
        ];
    }
}

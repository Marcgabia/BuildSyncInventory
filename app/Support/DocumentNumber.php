<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Document numbers in the format PREFIX-YYYYMMDD-0001 (A3).
 * The counter starts again at 0001 every day.
 *
 * Call it inside DB::transaction(): the sequence row stays locked until the
 * transaction ends, so two requests at the same time never get the same number.
 */
class DocumentNumber
{
    public static function next(string $prefix, int $digits = 4): string
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('DocumentNumber::next() must be called inside a database transaction.');
        }

        $key = $prefix.'-'.now()->format('Ymd');

        DB::table('document_sequences')->insertOrIgnore(['sequence_key' => $key, 'last_number' => 0]);

        $last = DB::table('document_sequences')
            ->where('sequence_key', $key)
            ->lockForUpdate()
            ->value('last_number');

        $number = $last + 1;

        DB::table('document_sequences')->where('sequence_key', $key)->update(['last_number' => $number]);

        return $key.'-'.str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
    }
}

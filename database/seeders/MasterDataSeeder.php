<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * B7 / A3: loads the shared sample data from database/seeders/data/*.csv
 * (from the "Team Integration – Master Data" Sheet, Bahagi C).
 *
 * Safe to run again: every table is upserted by its code, and the opening
 * stock uses the reference OPEN-<sku>-<warehouse_code>, so it is never added twice.
 *
 *   php artisan db:seed --class=MasterDataSeeder
 */
class MasterDataSeeder extends Seeder
{
    /** Users created for local testing (A3). */
    private const USERS = [
        'EMP-0001' => 'Admin',
        'EMP-0002' => 'Manager',
        'EMP-0007' => 'Inventory Clerk',
    ];

    public function run(): void
    {
        $now = now();

        // --- Copies from HR -------------------------------------------------
        $this->upsert('branches', 'branch_code', $this->csv('branches.csv'), fn ($r) => [
            'branch_code' => $r['branch_code'],
            'name' => $r['name'],
            'city' => $r['city'] ?: null,
            'status' => $r['status'],
        ], $now);

        $this->upsert('departments', 'department_code', $this->csv('departments.csv'), fn ($r) => [
            'department_code' => $r['department_code'],
            'name' => $r['name'],
            'status' => $r['status'],
        ], $now);

        $this->upsert('employees', 'employee_no', $this->csv('employees.csv'), fn ($r) => [
            'employee_no' => $r['employee_no'],
            'first_name' => $r['first_name'],
            'last_name' => $r['last_name'],
            'email' => $r['email'] ?: null,
            'branch_code' => $r['branch_code'] ?: null,
            'department_code' => $r['department_code'] ?: null,
            'position' => $r['position'] ?: null,
            'employment_status' => $r['employment_status'],
            // B7: can_login is true only for Active or On Leave.
            'can_login' => in_array($r['employment_status'], ['Active', 'On Leave'], true),
        ], $now);

        // --- Copies from PROC -----------------------------------------------
        $this->upsert('suppliers', 'supplier_code', $this->csv('suppliers.csv'), fn ($r) => [
            'supplier_code' => $r['supplier_code'],
            'name' => $r['name'],
            'contact_person' => $r['contact_person'] ?: null,
            'email' => $r['email'] ?: null,
            'phone' => $r['phone'] ?: null,
            'status' => $r['status'],
        ], $now);

        $this->upsert('supplier_products', ['supplier_code', 'sku'], $this->csv('supplier_products.csv'), fn ($r) => [
            'supplier_code' => $r['supplier_code'],
            'sku' => $r['sku'],
            'unit_cost' => $r['unit_cost'],
            'lead_time_days' => (int) $r['lead_time_days'],
            'is_preferred' => (bool) (int) $r['is_preferred'],
        ], $now);

        // --- INV's own data -------------------------------------------------
        $this->upsert('products', 'sku', $this->csv('products.csv'), fn ($r) => [
            'sku' => $r['sku'],
            'name' => $r['name'],
            'category' => $r['category'] ?: null,
            'unit' => $r['unit'] ?: null,
            'price' => $r['price'],
            'cost' => $r['cost'],
            'reorder_level' => (int) $r['reorder_level'],
            'reorder_quantity' => (int) $r['reorder_quantity'],
            'preferred_supplier_code' => $r['preferred_supplier_code'] ?: null,
            'status' => 'Active',
        ], $now);

        $this->upsert('warehouses', 'warehouse_code', $this->csv('warehouses.csv'), fn ($r) => [
            'warehouse_code' => $r['warehouse_code'],
            'name' => $r['name'],
            'branch_code' => $r['branch_code'],
            'is_default' => (bool) (int) $r['is_default'],
            'status' => $r['status'],
        ], $now);

        $this->openingBalances();
        $this->users();
    }

    /**
     * initial_stock.csv goes in as OPENING_BALANCE movements (A3), never as a
     * direct edit of stocks (A2): stock, movement and item are written together.
     *
     * Hakbang 7 moves this into StockMovementService::post(), which the API and
     * the screens will use too.
     */
    private function openingBalances(): void
    {
        $posted = 0;

        foreach ($this->csv('initial_stock.csv') as $r) {
            $reference = "OPEN-{$r['sku']}-{$r['warehouse_code']}";
            $quantity = (int) $r['quantity'];

            DB::transaction(function () use ($r, $reference, $quantity, &$posted) {
                $exists = DB::table('stock_movements')
                    ->where('source_system', 'INV')
                    ->where('reference_number', $reference)
                    ->exists();

                if ($exists || $quantity < 1) {
                    return;
                }

                $branchCode = DB::table('warehouses')->where('warehouse_code', $r['warehouse_code'])->value('branch_code')
                    ?? throw new RuntimeException("Unknown warehouse {$r['warehouse_code']} in initial_stock.csv");

                $now = now();
                DB::table('stocks')->insertOrIgnore([
                    'sku' => $r['sku'], 'warehouse_code' => $r['warehouse_code'],
                    'quantity' => 0, 'created_at' => $now, 'updated_at' => $now,
                ]);

                $stock = DB::table('stocks')
                    ->where('sku', $r['sku'])->where('warehouse_code', $r['warehouse_code'])
                    ->lockForUpdate()->first();

                $before = (int) $stock->quantity;
                $after = $before + $quantity;

                DB::table('stocks')->where('id', $stock->id)->update(['quantity' => $after, 'updated_at' => $now]);

                $movementId = DB::table('stock_movements')->insertGetId([
                    'movement_number' => DocumentNumber::next('SM'),
                    'movement_type' => 'OPENING_BALANCE',
                    'source_system' => 'INV',
                    'reference_number' => $reference,
                    'branch_code' => $branchCode,
                    'warehouse_code' => $r['warehouse_code'],
                    'performed_by' => null,
                    'remarks' => 'Opening balance (MasterDataSeeder)',
                    'status' => 'posted',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('stock_movement_items')->insert([
                    'stock_movement_id' => $movementId,
                    'sku' => $r['sku'],
                    'quantity' => $quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                ]);

                $posted++;
            });
        }

        $this->command?->info("Opening balances posted: {$posted} (the rest already existed).");
    }

    /** Local test accounts (A3). The password is only set when the user is first created. */
    private function users(): void
    {
        $password = env('SEED_DEFAULT_PASSWORD', 'password');

        foreach (self::USERS as $employeeNo => $role) {
            $employee = DB::table('employees')->where('employee_no', $employeeNo)->first()
                ?? throw new RuntimeException("{$employeeNo} is missing from employees.csv");

            $user = User::firstOrNew(['employee_no' => $employeeNo]);

            if (! $user->exists) {
                $user->password = $password;
            }

            $user->fill([
                'name' => "{$employee->first_name} {$employee->last_name}",
                'email' => $employee->email,
                'employment_status' => $employee->employment_status,
                'can_login' => (bool) $employee->can_login,
                'role' => $role,
            ])->save();
        }
    }

    /** Upsert rows by their code, keeping created_at of existing rows. */
    private function upsert(string $table, string|array $uniqueBy, array $rows, callable $map, $now): void
    {
        $data = array_map(fn ($r) => $map($r) + ['created_at' => $now, 'updated_at' => $now], $rows);
        $update = array_values(array_diff(array_keys($data[0]), (array) $uniqueBy, ['created_at']));

        DB::table($table)->upsert($data, $uniqueBy, $update);

        $this->command?->info(str_pad($table, 18).count($data).' rows');
    }

    /** Reads database/seeders/data/<file> into a list of [column => value] rows. */
    private function csv(string $file): array
    {
        $path = database_path("seeders/data/{$file}");
        $handle = fopen($path, 'r') ?: throw new RuntimeException("Cannot open {$path}");

        $header = array_map('trim', fgetcsv($handle, escape: '\\'));
        $rows = [];

        while (($line = fgetcsv($handle, escape: '\\')) !== false) {
            if ($line === [null]) {
                continue; // blank line
            }
            $rows[] = array_combine($header, array_map('trim', array_pad($line, count($header), '')));
        }

        fclose($handle);

        return $rows;
    }
}

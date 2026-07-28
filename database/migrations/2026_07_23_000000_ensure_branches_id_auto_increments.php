<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branches') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();
        $idColumn = DB::selectOne(
            <<<'SQL'
                SELECT COLUMN_NAME AS column_name, EXTRA AS extra
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'branches'
                  AND COLUMN_NAME = 'id'
                SQL,
            [$database],
        );

        if (! $idColumn) {
            throw new RuntimeException(
                'Cannot repair branches: the table does not contain an id column.',
            );
        }

        $otherAutoIncrementColumn = DB::selectOne(
            <<<'SQL'
                SELECT COLUMN_NAME AS column_name
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'branches'
                  AND COLUMN_NAME <> 'id'
                  AND EXTRA LIKE '%auto_increment%'
                LIMIT 1
                SQL,
            [$database],
        );

        if ($otherAutoIncrementColumn) {
            throw new RuntimeException(sprintf(
                'Cannot repair branches.id: branches.%s is already AUTO_INCREMENT.',
                $otherAutoIncrementColumn->column_name,
            ));
        }

        $primaryKeyColumns = DB::select(
            <<<'SQL'
                SELECT COLUMN_NAME AS column_name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'branches'
                  AND INDEX_NAME = 'PRIMARY'
                ORDER BY SEQ_IN_INDEX
                SQL,
            [$database],
        );

        if ($primaryKeyColumns === []) {
            if (DB::table('branches')->whereNull('id')->exists()) {
                throw new RuntimeException(
                    'Cannot make branches.id a primary key because it contains NULL values.',
                );
            }

            $duplicateId = DB::selectOne(
                <<<'SQL'
                    SELECT id
                    FROM branches
                    GROUP BY id
                    HAVING COUNT(*) > 1
                    LIMIT 1
                    SQL,
            );

            if ($duplicateId) {
                throw new RuntimeException(sprintf(
                    'Cannot make branches.id a primary key because id %s is duplicated.',
                    $duplicateId->id,
                ));
            }

            DB::statement('ALTER TABLE `branches` ADD PRIMARY KEY (`id`)');
        } elseif (
            count($primaryKeyColumns) !== 1
            || $primaryKeyColumns[0]->column_name !== 'id'
        ) {
            $primaryKey = implode(
                ', ',
                array_map(
                    static fn (object $column): string => $column->column_name,
                    $primaryKeyColumns,
                ),
            );

            throw new RuntimeException(sprintf(
                'Cannot safely repair branches.id because the existing primary key is (%s), not (id).',
                $primaryKey,
            ));
        }

        if (! str_contains(strtolower($idColumn->extra), 'auto_increment')) {
            DB::statement(
                'ALTER TABLE `branches` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            );
        }
    }

    public function down(): void
    {
        // This repairs the primary key definition and should not be reversed.
    }
};

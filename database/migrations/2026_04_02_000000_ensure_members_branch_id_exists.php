<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members') || Schema::hasColumn('members', 'branch_id')) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
        });

        if (Schema::hasTable('branches')) {
            Schema::table('members', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('members') || ! Schema::hasColumn('members', 'branch_id')) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasTable('branches')) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Throwable $e) {
                    // Ignore when FK does not exist.
                }
            }

            $table->dropColumn('branch_id');
        });
    }
};

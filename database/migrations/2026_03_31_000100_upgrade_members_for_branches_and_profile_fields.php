<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        $columns = [
            'branch_id',
            'title',
            'surname',
            'other_names',
            'date_of_birth',
            'place_of_birth',
            'town_of_origin',
            'village',
            'local_government_of_origin',
            'state_of_origin',
            'occupation',
            'height',
            'next_of_kin_name',
            'next_of_kin_relationship',
            'next_of_kin_phone',
            'father_name',
            'mother_name',
            'wife_name',
            'house_address',
            'office_address',
        ];

        $missing = array_fill_keys($columns, false);

        foreach ($columns as $column) {
            $missing[$column] = ! Schema::hasColumn('members', $column);
        }

        Schema::table('members', function (Blueprint $table) use ($missing) {
            if ($missing['branch_id']) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('id');
            }

            if ($missing['title']) {
                $table->string('title', 30)->nullable()->after('branch_id');
            }

            if ($missing['surname']) {
                $table->string('surname', 120)->nullable()->after('title');
            }

            if ($missing['other_names']) {
                $table->string('other_names', 160)->nullable()->after('surname');
            }

            if ($missing['date_of_birth']) {
                $table->date('date_of_birth')->nullable()->after('other_names');
            }

            if ($missing['place_of_birth']) {
                $table->string('place_of_birth')->nullable()->after('date_of_birth');
            }

            if ($missing['town_of_origin']) {
                $table->string('town_of_origin')->nullable()->after('place_of_birth');
            }

            if ($missing['village']) {
                $table->string('village')->nullable()->after('town_of_origin');
            }

            if ($missing['local_government_of_origin']) {
                $table->string('local_government_of_origin')->nullable()->after('village');
            }

            if ($missing['state_of_origin']) {
                $table->string('state_of_origin')->nullable()->after('local_government_of_origin');
            }

            if ($missing['occupation']) {
                $table->string('occupation')->nullable()->after('state_of_origin');
            }

            if ($missing['height']) {
                $table->string('height', 50)->nullable()->after('occupation');
            }

            if ($missing['next_of_kin_name']) {
                $table->string('next_of_kin_name')->nullable()->after('phone_number');
            }

            if ($missing['next_of_kin_relationship']) {
                $table->string('next_of_kin_relationship')->nullable()->after('next_of_kin_name');
            }

            if ($missing['next_of_kin_phone']) {
                $table->string('next_of_kin_phone', 50)->nullable()->after('next_of_kin_relationship');
            }

            if ($missing['father_name']) {
                $table->string('father_name')->nullable()->after('next_of_kin_phone');
            }

            if ($missing['mother_name']) {
                $table->string('mother_name')->nullable()->after('father_name');
            }

            if ($missing['wife_name']) {
                $table->string('wife_name')->nullable()->after('mother_name');
            }

            if ($missing['house_address']) {
                $table->text('house_address')->nullable()->after('wife_name');
            }

            if ($missing['office_address']) {
                $table->text('office_address')->nullable()->after('house_address');
            }
        });

    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'branch_id',
                'title',
                'surname',
                'other_names',
                'date_of_birth',
                'place_of_birth',
                'town_of_origin',
                'village',
                'local_government_of_origin',
                'state_of_origin',
                'occupation',
                'height',
                'next_of_kin_name',
                'next_of_kin_relationship',
                'next_of_kin_phone',
                'father_name',
                'mother_name',
                'wife_name',
                'house_address',
                'office_address',
            ]);
        });
    }
};

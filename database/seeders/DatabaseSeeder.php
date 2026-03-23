<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@dsfc.ng',
        ], [
            'name' => 'dsfcadmin',
            'email' => 'admin@dsfc.ng',
            'password' => Hash::make('Admin@12345'),
            'email_verified_at' => now(),
        ]);
    }
}

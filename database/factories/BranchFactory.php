<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Lagos Branch',
                'Anambra Branch',
                'Enugu Branch',
                'Abuja Branch',
                'Port Harcourt Branch',
            ]) . ' ' . fake()->numberBetween(1, 99),
            'is_active' => true,
        ];
    }
}

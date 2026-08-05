<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $surname = fake()->lastName();
        $otherNames = fake()->firstName() . ' ' . fake()->firstName();
        $houseAddress = fake()->address();

        return [
            'full_name' => trim($surname . ' ' . $otherNames),
            'address' => $houseAddress,
            'branch_id' => Branch::factory(),
            'title' => fake()->optional()->randomElement(['Mr', 'Mrs', 'Miss', 'Dr']),
            'surname' => $surname,
            'other_names' => $otherNames,
            'date_of_birth' => fake()->optional()->date(),
            'place_of_birth' => fake()->optional()->city(),
            'town_of_origin' => fake()->optional()->city(),
            'village' => fake()->optional()->streetName(),
            'local_government_of_origin' => fake()->optional()->city(),
            'state_of_origin' => fake()->optional()->state(),
            'occupation' => fake()->optional()->jobTitle(),
            'height' => fake()->optional()->numerify('1##cm'),
            'phone_number' => fake()->numerify('080########'),
            'next_of_kin_name' => fake()->optional()->name(),
            'next_of_kin_relationship' => fake()->optional()->randomElement(['Father', 'Mother', 'Spouse', 'Sibling']),
            'next_of_kin_phone' => fake()->optional()->numerify('080########'),
            'father_name' => fake()->optional()->name('male'),
            'mother_name' => fake()->optional()->name('female'),
            'wife_name' => fake()->optional()->name('female'),
            'house_address' => $houseAddress,
            'office_address' => fake()->optional()->address(),
            'photo' => null,
            'dynamic_fields' => null,
        ];
    }
}

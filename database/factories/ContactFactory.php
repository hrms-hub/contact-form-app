<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->unique()->safeEmail(),
            'tel' => fake()->numerify(
                fake()->randomElement([
                    '0#########',
                    '0##########',
                ])
            ),
            'address' => fake()->address(),
            'building' => fake()->secondaryAddress(),
            'detail' => fake()->realText(120),
        ];
    }
}

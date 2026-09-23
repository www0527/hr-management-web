<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Positions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first_name = fake()->firstName();
        $last_name = fake()->lastName();

        return [
            'user_id' => null,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'department_id' => Department::factory(),
            'position_id' => Positions::factory(),
            'manager_id' => null,
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'employment_status' => 'active',
            'salary' => fake()->numberBetween(35000, 140000),
            'avatar_path' => null,
            'address' => fake()->address(),
        ];
    }
}

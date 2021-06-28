<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

// S C:\Users\Lenovo\Desktop\news-system-backend> php artisan passport:install
// Encryption keys generated successfully.
// Personal access client created successfully.
// Client ID: 1
// Client secret: i4LYv8nifhrBFdeIR65vytYO0VqMYXjsWynZTGfI
// Password grant client created successfully.
// Client ID: 2
// Client secret: HnqXiGwcx6PGqf14zOh1RjVlHjxiPpk8M1XwpTNl
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fname' => $this->faker->name,
            // 'lname' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->name(),
            'role_id' => '1',
            // 'email_verified_at' => now(),
            'password' => '123',
            // 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

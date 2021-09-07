<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class UserFactory
 *
 * @package Database\Factories
 */
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
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'image_name' => 'test.png',
            'role_id' => UserService::ROLE_USER,
            'password' => bcrypt(1234),
        ];
    }
}

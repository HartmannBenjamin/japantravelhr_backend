<?php

namespace Database\Factories;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RequestFactory
 *
 * @package Database\Factories
 */
class RequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Request::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'subject' => $this->faker->text(40),
            'description' => $this->faker->text(150),
            'user_id' => 1,
        ];
    }
}

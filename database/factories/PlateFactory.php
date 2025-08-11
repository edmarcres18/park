<?php

namespace Database\Factories;

use App\Models\Plate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plate>
 */
class PlateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Plate>
     */
    protected $model = Plate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => strtoupper($this->faker->bothify('???-####')),
            'owner_name' => $this->faker->name(),
            'vehicle_type' => $this->faker->randomElement(['Car', 'SUV', 'Van', 'Truck', 'Motorcycle', 'Bus']),
        ];
    }
}



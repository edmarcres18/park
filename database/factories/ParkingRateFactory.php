<?php

namespace Database\Factories;

use App\Models\ParkingRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingRate>
 */
class ParkingRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rateTypes = ['hourly', 'minutely'];
        $rateType = $this->faker->randomElement($rateTypes);
        
        return [
            'name' => $this->faker->randomElement([
                'Standard Rate',
                'Premium Rate', 
                'Weekend Rate',
                'Holiday Rate',
                'Night Rate',
                'Early Bird Rate'
            ]),
            'rate_type' => $rateType,
            'rate_amount' => $rateType === 'hourly' 
                ? $this->faker->randomFloat(2, 20, 150) // ₱20-₱150 per hour
                : $this->faker->randomFloat(2, 0.50, 5.00), // ₱0.50-₱5.00 per minute
            'grace_period' => $this->faker->optional(0.7)->numberBetween(0, 60), // 70% chance of having grace period
            'is_active' => false, // Default to false, we'll set one active manually
            'description' => $this->faker->optional(0.6)->sentence(
                $this->faker->numberBetween(6, 12)
            ),
        ];
    }

    /**
     * Indicate that the parking rate is active.
     *
     * @return static
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the parking rate is hourly.
     *
     * @return static
     */
    public function hourly()
    {
        return $this->state(function (array $attributes) {
            return [
                'rate_type' => 'hourly',
                'rate_amount' => $this->faker->randomFloat(2, 25, 200), // ₱25-₱200 per hour
            ];
        });
    }

    /**
     * Indicate that the parking rate is per minute.
     *
     * @return static
     */
    public function minutely()
    {
        return $this->state(function (array $attributes) {
            return [
                'rate_type' => 'minutely',
                'rate_amount' => $this->faker->randomFloat(2, 0.50, 3.50), // ₱0.50-₱3.50 per minute
            ];
        });
    }

    /**
     * Indicate that the parking rate has no grace period.
     *
     * @return static
     */
    public function noGracePeriod()
    {
        return $this->state(function (array $attributes) {
            return [
                'grace_period' => null,
            ];
        });
    }
}

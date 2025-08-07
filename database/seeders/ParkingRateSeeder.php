<?php

namespace Database\Seeders;

use App\Models\ParkingRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParkingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample parking rates
        $rates = [
            [
                'name' => 'Standard Hourly Rate',
                'rate_type' => 'hourly',
                'rate_amount' => 50.00, // ₱50 per hour
                'grace_period' => 15,
                'is_active' => true, // This will be the active rate
                'description' => 'Standard parking rate for regular hours with 15-minute grace period.'
            ],
            [
                'name' => 'Premium Hourly Rate',
                'rate_type' => 'hourly',
                'rate_amount' => 80.00, // ₱80 per hour
                'grace_period' => 10,
                'is_active' => false,
                'description' => 'Premium parking rate for high-demand periods and shopping malls.'
            ],
            [
                'name' => 'Per-Minute Rate',
                'rate_type' => 'minutely',
                'rate_amount' => 1.50, // ₱1.50 per minute
                'grace_period' => 5,
                'is_active' => false,
                'description' => 'Flexible per-minute billing for short-term parking and quick errands.'
            ],
            [
                'name' => 'Night Rate',
                'rate_type' => 'hourly',
                'rate_amount' => 30.00, // ₱30 per hour
                'grace_period' => 30,
                'is_active' => false,
                'description' => 'Discounted rate for overnight parking (6 PM - 6 AM) with extended grace period.'
            ],
            [
                'name' => 'Weekend Rate',
                'rate_type' => 'hourly',
                'rate_amount' => 65.00, // ₱65 per hour
                'grace_period' => 20,
                'is_active' => false,
                'description' => 'Special weekend rate for Saturday and Sunday with extended grace period.'
            ],
        ];

        foreach ($rates as $rateData) {
            ParkingRate::create($rateData);
        }

        $this->command->info('Parking rates seeded successfully!');
    }
}

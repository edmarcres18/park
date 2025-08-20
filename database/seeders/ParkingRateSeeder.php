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
                'name' => 'Hourly Rate',
                'rate_type' => 'hourly',
                'rate_amount' => 50.00, // ₱50 per hour
                'grace_period' => 0,
                'is_active' => true, // This will be the active rate
                'description' => 'Hourly parking rate for regular hours with 10-minute grace period.'
            ],
        ];

        foreach ($rates as $rateData) {
            ParkingRate::create($rateData);
        }

        $this->command->info('Parking rates seeded successfully!');
    }
}

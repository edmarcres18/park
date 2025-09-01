<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Cebu Branch',
                'code' => 'CEB',
                'address' => 'IT Park, Lahug, Cebu City, Cebu, Philippines',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CDO Branch',
                'code' => 'CDO',
                'address' => 'Limketkai Center, Cagayan de Oro City, Misamis Oriental, Philippines',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Makati Branch',
                'code' => 'MKT',
                'address' => 'Ayala Avenue, Makati City, Metro Manila, Philippines',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch
            );
        }
    }
}

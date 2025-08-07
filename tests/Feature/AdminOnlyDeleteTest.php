<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ParkingRate;
use App\Models\Plate;
use App\Models\ParkingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class AdminOnlyDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'attendant']);
    }

    /** @test */
    public function admin_can_delete_parking_rates()
    {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        // Create a parking rate
        $rate = ParkingRate::factory()->create();
        
        // Admin should be able to delete
        $response = $this->actingAs($admin)
            ->delete(route('admin.rates.destroy', $rate));
            
        $response->assertRedirect();
        $this->assertDatabaseMissing('parking_rates', ['id' => $rate->id]);
    }

    /** @test */
    public function non_admin_cannot_delete_parking_rates()
    {
        // Create attendant user
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');
        
        // Create a parking rate
        $rate = ParkingRate::factory()->create();
        
        // Attendant should not be able to delete
        $response = $this->actingAs($attendant)
            ->delete(route('admin.rates.destroy', $rate));
            
        $response->assertStatus(403);
        $this->assertDatabaseHas('parking_rates', ['id' => $rate->id]);
    }

    /** @test */
    public function admin_can_delete_plates()
    {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        // Create a plate
        $plate = Plate::factory()->create();
        
        // Admin should be able to delete
        $response = $this->actingAs($admin)
            ->delete(route('admin.plates.destroy', $plate));
            
        $response->assertRedirect();
        $this->assertDatabaseMissing('plates', ['id' => $plate->id]);
    }

    /** @test */
    public function non_admin_cannot_delete_plates()
    {
        // Create attendant user
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');
        
        // Create a plate
        $plate = Plate::factory()->create();
        
        // Attendant should not be able to delete
        $response = $this->actingAs($attendant)
            ->delete(route('admin.plates.destroy', $plate));
            
        $response->assertStatus(403);
        $this->assertDatabaseHas('plates', ['id' => $plate->id]);
    }

    /** @test */
    public function admin_can_delete_parking_sessions()
    {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        // Create dependencies
        $plate = Plate::factory()->create();
        $rate = ParkingRate::factory()->create();
        
        // Create a parking session
        $session = ParkingSession::create([
            'plate_number' => $plate->number,
            'start_time' => now(),
            'created_by' => $admin->id,
            'parking_rate_id' => $rate->id,
        ]);
        
        // Admin should be able to delete
        $response = $this->actingAs($admin)
            ->delete(route('admin.sessions.destroy', $session));
            
        $response->assertRedirect();
        $this->assertDatabaseMissing('parking_sessions', ['id' => $session->id]);
    }

    /** @test */
    public function non_admin_cannot_delete_parking_sessions()
    {
        // Create attendant user
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');
        
        // Create dependencies
        $plate = Plate::factory()->create();
        $rate = ParkingRate::factory()->create();
        
        // Create a parking session
        $session = ParkingSession::create([
            'plate_number' => $plate->number,
            'start_time' => now(),
            'created_by' => $attendant->id,
            'parking_rate_id' => $rate->id,
        ]);
        
        // Attendant should not be able to delete
        $response = $this->actingAs($attendant)
            ->delete(route('admin.sessions.destroy', $session));
            
        $response->assertStatus(403);
        $this->assertDatabaseHas('parking_sessions', ['id' => $session->id]);
    }
}

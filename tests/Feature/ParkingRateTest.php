<?php

namespace Tests\Feature;

use App\Models\ParkingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParkingRateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'attendant']);
    }

    protected function createAdminUser()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    /** @test */
    public function admin_can_view_parking_rates_index()
    {
        $admin = $this->createAdminUser();
        ParkingRate::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.rates.index'));

        $response->assertStatus(200)
                 ->assertViewHas('rates')
                 ->assertViewHas('activeRate');
    }

    /** @test */
    public function non_admin_cannot_access_parking_rates()
    {
        $user = User::factory()->create();
        $user->assignRole('attendant');

        $response = $this->actingAs($user)->get(route('admin.rates.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_parking_rate()
    {
        $admin = $this->createAdminUser();

        $rateData = [
            'name' => 'Test Rate',
            'rate_type' => 'hourly',
            'rate_amount' => 50.00, // ₱50 per hour
            'grace_period' => 15,
            'is_active' => true,
            'description' => 'Test parking rate description'
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.rates.store'), $rateData);

        $response->assertRedirect(route('admin.rates.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('parking_rates', [
            'name' => 'Test Rate',
            'rate_type' => 'hourly',
            'rate_amount' => '50.00',
            'grace_period' => 15,
            'is_active' => true
        ]);
    }

    /** @test */
    public function only_one_rate_can_be_active_at_a_time()
    {
        $admin = $this->createAdminUser();
        
        // Create first active rate
        $firstRate = ParkingRate::factory()->active()->create();
        $this->assertTrue($firstRate->fresh()->is_active);

        // Create second rate and make it active
        $secondRateData = [
            'name' => 'Second Rate',
            'rate_type' => 'minutely',
            'rate_amount' => 0.25,
            'is_active' => true
        ];

        $this->actingAs($admin)
             ->post(route('admin.rates.store'), $secondRateData);

        // First rate should now be inactive
        $this->assertFalse($firstRate->fresh()->is_active);
        
        // Second rate should be active
        $secondRate = ParkingRate::where('name', 'Second Rate')->first();
        $this->assertTrue($secondRate->is_active);
    }

    /** @test */
    public function admin_can_update_parking_rate()
    {
        $admin = $this->createAdminUser();
        $rate = ParkingRate::factory()->create([
            'name' => 'Original Rate',
            'rate_amount' => 3.00
        ]);

        $updateData = [
            'name' => 'Updated Rate',
            'rate_type' => $rate->rate_type,
            'rate_amount' => 7.50,
            'grace_period' => $rate->grace_period,
            'is_active' => false,
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($admin)
                         ->put(route('admin.rates.update', $rate), $updateData);

        $response->assertRedirect(route('admin.rates.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('parking_rates', [
            'id' => $rate->id,
            'name' => 'Updated Rate',
            'rate_amount' => '7.50'
        ]);
    }

    /** @test */
    public function admin_can_delete_inactive_parking_rate()
    {
        $admin = $this->createAdminUser();
        $rate = ParkingRate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)
                         ->delete(route('admin.rates.destroy', $rate));

        $response->assertRedirect(route('admin.rates.index'))
                 ->assertSessionHas('success');

        $this->assertSoftDeleted('parking_rates', ['id' => $rate->id]);
    }

    /** @test */
    public function admin_cannot_delete_active_parking_rate()
    {
        $admin = $this->createAdminUser();
        $rate = ParkingRate::factory()->active()->create();

        $response = $this->actingAs($admin)
                         ->delete(route('admin.rates.destroy', $rate));

        $response->assertRedirect(route('admin.rates.index'))
                 ->assertSessionHas('error');

        $this->assertDatabaseHas('parking_rates', ['id' => $rate->id]);
    }

    /** @test */
    public function admin_can_activate_parking_rate()
    {
        $admin = $this->createAdminUser();
        $activeRate = ParkingRate::factory()->active()->create();
        $inactiveRate = ParkingRate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin)
                         ->put(route('admin.rates.activate', $inactiveRate));

        $response->assertRedirect(route('admin.rates.index'))
                 ->assertSessionHas('success');

        // Previously active rate should now be inactive
        $this->assertFalse($activeRate->fresh()->is_active);
        
        // Target rate should now be active
        $this->assertTrue($inactiveRate->fresh()->is_active);
    }

    /** @test */
    public function parking_rate_requires_valid_data()
    {
        $admin = $this->createAdminUser();

        // Test missing required fields
        $response = $this->actingAs($admin)
                         ->post(route('admin.rates.store'), []);

        $response->assertSessionHasErrors(['rate_type', 'rate_amount']);

        // Test invalid rate type
        $response = $this->actingAs($admin)
                         ->post(route('admin.rates.store'), [
                             'rate_type' => 'invalid',
                             'rate_amount' => 5.00
                         ]);

        $response->assertSessionHasErrors(['rate_type']);

        // Test negative rate amount
        $response = $this->actingAs($admin)
                         ->post(route('admin.rates.store'), [
                             'rate_type' => 'hourly',
                             'rate_amount' => -1.00
                         ]);

        $response->assertSessionHasErrors(['rate_amount']);
    }

    /** @test */
    public function parking_rate_calculates_fees_correctly()
    {
        // Test hourly rate without grace period
        $hourlyRate = ParkingRate::factory()->create([
            'rate_type' => 'hourly',
            'rate_amount' => 5.00,
            'grace_period' => null
        ]);

        // 90 minutes should be 2 hours (rounded up)
        $this->assertEquals(10.00, $hourlyRate->calculateFee(90));
        
        // 120 minutes should be exactly 2 hours
        $this->assertEquals(10.00, $hourlyRate->calculateFee(120));

        // Test minutely rate with grace period
        $minutelyRate = ParkingRate::factory()->create([
            'rate_type' => 'minutely',
            'rate_amount' => 0.25,
            'grace_period' => 15
        ]);

        // 10 minutes should be free (within grace period)
        $this->assertEquals(0.00, $minutelyRate->calculateFee(10));
        
        // 20 minutes should charge for 5 minutes (20 - 15 grace)
        $this->assertEquals(1.25, $minutelyRate->calculateFee(20));
    }
}

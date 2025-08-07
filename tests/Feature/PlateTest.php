<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Plate;
use Spatie\Permission\Models\Role;

class PlateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $attendant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'attendant']);

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->attendant = User::factory()->create();
        $this->attendant->assignRole('attendant');
    }

    // Admin Blade Tests

    /** @test */
    public function admin_can_view_plates_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.plates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.plates.index');
    }

    /** @test */
    public function admin_can_create_plate()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.plates.store'), [
            'number' => 'TEST-123',
            'owner_name' => 'Test Owner',
            'vehicle_type' => 'Car',
        ]);

        $response->assertRedirect(route('admin.plates.index'));
        $this->assertDatabaseHas('plates', ['number' => 'TEST-123']);
    }

    /** @test */
    public function admin_can_update_plate()
    {
        $plate = Plate::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.plates.update', $plate), [
            'number' => 'UPDATED-123',
            'owner_name' => 'Updated Owner',
            'vehicle_type' => 'Motorcycle',
        ]);

        $response->assertRedirect(route('admin.plates.index'));
        $this->assertDatabaseHas('plates', ['number' => 'UPDATED-123']);
    }

    /** @test */
    public function admin_can_delete_plate()
    {
        $plate = Plate::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.plates.destroy', $plate));

        $response->assertRedirect(route('admin.plates.index'));
        $this->assertDatabaseMissing('plates', ['id' => $plate->id]);
    }

    // Attendant Blade Tests

    /** @test */
    public function attendant_can_view_plates_index()
    {
        $response = $this->actingAs($this->attendant)->get(route('attendant.plates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('attendant.plates.index');
    }

    /** @test */
    public function attendant_can_create_plate()
    {
        $response = $this->actingAs($this->attendant)->post(route('attendant.plates.store'), [
            'number' => 'TEST-456',
            'owner_name' => 'Test Owner 2',
            'vehicle_type' => 'Van',
        ]);

        $response->assertRedirect(route('attendant.plates.index'));
        $this->assertDatabaseHas('plates', ['number' => 'TEST-456']);
    }

    /** @test */
    public function attendant_can_update_plate()
    {
        $plate = Plate::factory()->create();

        $response = $this->actingAs($this->attendant)->put(route('attendant.plates.update', $plate), [
            'number' => 'UPDATED-456',
            'owner_name' => 'Updated Owner 2',
            'vehicle_type' => 'Bus',
        ]);

        $response->assertRedirect(route('attendant.plates.index'));
        $this->assertDatabaseHas('plates', ['number' => 'UPDATED-456']);
    }

    /** @test */
    public function attendant_can_delete_plate()
    {
        $plate = Plate::factory()->create();

        $response = $this->actingAs($this->attendant)->delete(route('attendant.plates.destroy', $plate));

        $response->assertRedirect(route('attendant.plates.index'));
        $this->assertDatabaseMissing('plates', ['id' => $plate->id]);
    }


    // API Tests

    /** @test */
    public function api_admin_can_get_all_plates()
    {
        Plate::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/plates');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function api_attendant_can_get_all_plates()
    {
        Plate::factory()->count(3)->create();

        $response = $this->actingAs($this->attendant, 'sanctum')->getJson('/api/plates');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }


    /** @test */
    public function api_admin_can_create_plate()
    {
        $data = [
            'number' => 'API-TEST-123',
            'owner_name' => 'API Test Owner',
            'vehicle_type' => 'SUV',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/plates', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('plates', $data);
    }

    /** @test */
    public function api_admin_can_update_plate()
    {
        $plate = Plate::factory()->create();
        $data = [
            'number' => 'API-UPDATED-123',
            'owner_name' => 'API Updated Owner',
            'vehicle_type' => 'Truck',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->putJson("/api/plates/{$plate->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plates', $data);
    }

    /** @test */
    public function api_admin_can_delete_plate()
    {
        $plate = Plate::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/plates/{$plate->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('plates', ['id' => $plate->id]);
    }
}

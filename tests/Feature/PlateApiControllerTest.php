<?php

namespace Tests\Feature;

use App\Models\Plate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PlateApiControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $attendant;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create attendant user
        $this->attendant = User::factory()->create([
            'role' => 'attendant',
            'status' => 'active'
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_validates_plate_number_format()
    {
        // Test valid formats
        $validFormats = [
            'ABC123',    // Old format
            'ABC 123',   // Old format with space
            'ABC1234',   // New format
            'ABC 1234',  // New format with space
            'AA12345',   // Motorcycle format
            'AA 12345',  // Motorcycle format with space
        ];

        foreach ($validFormats as $format) {
            $response = $this->actingAs($this->attendant)
                ->postJson('/api/plates', [
                    'number' => $format,
                    'owner_name' => 'John Doe',
                    'vehicle_type' => 'Car'
                ]);

            if ($response->status() === 422) {
                $this->fail("Valid format '{$format}' was rejected: " . json_encode($response->json()));
            }

            $this->assertDatabaseHas('plates', [
                'number' => $format,
                'owner_name' => 'John Doe',
                'vehicle_type' => 'Car'
            ]);
        }
    }

    /** @test */
    public function it_rejects_invalid_plate_number_formats()
    {
        $invalidFormats = [
            'ABC12',     // Too short
            'ABC12345',  // Too long for car format
            'A12345',    // Only one letter
            'ABC123A',   // Letter after numbers
            '123ABC',    // Numbers first
            'A-B-C123',  // Contains hyphens
            'ABC 12',    // Too few numbers
            'AA 1234',   // Wrong motorcycle length
        ];

        foreach ($invalidFormats as $format) {
            $response = $this->actingAs($this->attendant)
                ->postJson('/api/plates', [
                    'number' => $format,
                    'owner_name' => 'John Doe',
                    'vehicle_type' => 'Car'
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['number']);
        }
    }

    /** @test */
    public function it_prevents_duplicate_plate_creation()
    {
        // Create a plate first
        $existingPlate = Plate::factory()->create([
            'number' => 'ABC 123'
        ]);

        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', [
                'number' => 'ABC 123', // Same number as existing plate
                'owner_name' => 'John Doe',
                'vehicle_type' => 'Car'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'A plate with this number already exists.'
            ])
            ->assertJsonStructure([
                'message',
                'existing_plate' => [
                    'id',
                    'number',
                    'owner_name',
                    'vehicle_type'
                ]
            ]);
    }

    /** @test */
    public function it_allows_creation_of_plate_with_unique_number()
    {
        $plateData = [
            'number' => 'XYZ 789',
            'owner_name' => 'Jane Smith',
            'vehicle_type' => 'SUV'
        ];

        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', $plateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'number' => 'XYZ 789',
                    'owner_name' => 'Jane Smith',
                    'vehicle_type' => 'SUV'
                ]
            ]);

        $this->assertDatabaseHas('plates', $plateData);
    }

    /** @test */
    public function it_can_check_if_plate_number_exists()
    {
        // Create a plate
        $plate = Plate::factory()->create([
            'number' => 'TEST 123'
        ]);

        // Check for existing plate
        $response = $this->actingAs($this->attendant)
            ->getJson('/api/plates/check-duplicate/TEST 123');

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
                'message' => 'A plate with this number already exists.'
            ])
            ->assertJsonStructure([
                'exists',
                'message',
                'plate' => [
                    'id',
                    'number',
                    'owner_name',
                    'vehicle_type'
                ]
            ]);

        // Check for non-existing plate
        $response = $this->actingAs($this->attendant)
            ->getJson('/api/plates/check-duplicate/NEW 456');

        $response->assertStatus(200)
            ->assertJson([
                'exists' => false,
                'message' => 'Plate number is available.'
            ])
            ->assertJsonMissing(['plate']);
    }

    /** @test */
    public function it_rejects_invalid_format_in_duplicate_check()
    {
        $response = $this->actingAs($this->attendant)
            ->getJson('/api/plates/check-duplicate/INVALID');

        $response->assertStatus(422)
            ->assertJson([
                'exists' => false,
                'message' => 'Plate number must follow the format: AAA 123, AAA 1234, or AA 12345.',
                'valid_format' => false
            ]);
    }

    /** @test */
    public function it_returns_error_for_empty_plate_number_check()
    {
        $response = $this->actingAs($this->attendant)
            ->getJson('/api/plates/check-duplicate/');

        $response->assertStatus(404); // Route not found for empty parameter
    }

    /** @test */
    public function it_logs_duplicate_attempt_activities()
    {
        // Create a plate first
        Plate::factory()->create([
            'number' => 'DUPLICATE 123'
        ]);

        $this->actingAs($this->attendant)
            ->postJson('/api/plates', [
                'number' => 'DUPLICATE 123',
                'owner_name' => 'Test User',
                'vehicle_type' => 'Car'
            ]);

        // Check if activity was logged
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'plate_api',
            'description' => 'Attempted to create duplicate plate DUPLICATE 123 via API'
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', [
                'owner_name' => 'John Doe'
                // Missing number and vehicle_type
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number', 'vehicle_type']);
    }

    /** @test */
    public function it_validates_string_fields()
    {
        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', [
                'number' => 123, // Should be string
                'owner_name' => 456, // Should be string
                'vehicle_type' => 789 // Should be string
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number', 'vehicle_type']);
    }

    /** @test */
    public function it_respects_max_length_validation()
    {
        $longString = str_repeat('A', 256); // Exceeds 255 character limit

        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', [
                'number' => $longString,
                'owner_name' => $longString,
                'vehicle_type' => $longString
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['number', 'owner_name', 'vehicle_type']);
    }

    /** @test */
    public function it_allows_owner_name_to_be_null()
    {
        $plateData = [
            'number' => 'NULL 123',
            'owner_name' => null,
            'vehicle_type' => 'Car'
        ];

        $response = $this->actingAs($this->attendant)
            ->postJson('/api/plates', $plateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('plates', [
            'number' => 'NULL 123',
            'owner_name' => null,
            'vehicle_type' => 'Car'
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/plates', [
            'number' => 'AUTH 123',
            'vehicle_type' => 'Car'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_attendant_role()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/plates', [
                'number' => 'ROLE 123',
                'vehicle_type' => 'Car'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_formats_plate_numbers_consistently()
    {
        $testCases = [
            'abc123' => 'ABC 123',
            'ABC123' => 'ABC 123',
            'ABC 123' => 'ABC 123',
            'abc1234' => 'ABC 1234',
            'ABC1234' => 'ABC 1234',
            'ABC 1234' => 'ABC 1234',
            'aa12345' => 'AA 12345',
            'AA12345' => 'AA 12345',
            'AA 12345' => 'AA 12345',
        ];

        foreach ($testCases as $input => $expected) {
            $response = $this->actingAs($this->attendant)
                ->postJson('/api/plates', [
                    'number' => $input,
                    'owner_name' => 'Test User',
                    'vehicle_type' => 'Car'
                ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('plates', [
                'number' => $expected,
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plate;
use App\Models\Rate;
use App\Models\Session;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'attendant']);
    }

    /** @test */
    public function admin_can_view_activity_logs_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200)
            ->assertViewIs('admin.activity-logs.index')
            ->assertViewHas(['activities', 'users', 'logNames']);
    }

    /** @test */
    public function non_admin_cannot_view_activity_logs_page()
    {
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');

        $response = $this->actingAs($attendant)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_activity_logs_page()
    {
        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_access_activity_logs_api()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/admin/activity-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function non_admin_cannot_access_activity_logs_api()
    {
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');
        $token = $attendant->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/admin/activity-logs');

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right roles.']);
    }

    /** @test */
    public function login_event_is_logged()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Clear existing activities
        Activity::truncate();

        // Perform login
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Check if login activity was logged
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'User logged in successfully',
            'causer_id' => $user->id,
            'causer_type' => User::class,
        ]);

        $activity = Activity::where('log_name', 'auth')
            ->where('description', 'User logged in successfully')
            ->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('ip', $activity->properties);
        $this->assertArrayHasKey('location', $activity->properties);
        $this->assertArrayHasKey('user_agent', $activity->properties);
    }

    /** @test */
    public function logout_event_is_logged()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Login first
        $this->actingAs($user);

        // Clear existing activities
        Activity::truncate();

        // Perform logout
        $response = $this->post(route('logout'));

        // Check if logout activity was logged
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'User logged out successfully',
            'causer_id' => $user->id,
            'causer_type' => User::class,
        ]);
    }

    /** @test */
    public function crud_operations_are_logged()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Clear existing activities
        Activity::truncate();

        // Test User creation
        $newUser = User::factory()->create();
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'default',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => 'created',
        ]);

        // Test User update
        $newUser->update(['name' => 'Updated Name']);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'default',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => 'updated',
        ]);

        // Test Plate creation
        $plate = Plate::create([
            'number' => 'TEST123',
            'owner_name' => 'Test Owner',
            'vehicle_type' => 'car',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'plate',
            'subject_id' => $plate->id,
            'subject_type' => Plate::class,
            'event' => 'created',
        ]);
    }

    /** @test */
    public function activity_log_filters_work_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $user1 = User::factory()->create(['name' => 'Test User 1']);
        $user2 = User::factory()->create(['name' => 'Test User 2']);

        // Create some test activities
        activity('test')->causedBy($user1)->log('Test action by user 1');
        activity('authentication')->causedBy($user2)->log('User logged in');

        // Test user filter
        $response = $this->actingAs($admin)
            ->get(route('admin.activity-logs.index', ['user_id' => $user1->id]));

        $response->assertStatus(200);

        // Test log name filter
        $response = $this->actingAs($admin)
            ->get(route('admin.activity-logs.index', ['log_name' => 'authentication']));

        $response->assertStatus(200);
    }

    /** @test */
    public function api_filters_work_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('test')->plainTextToken;

        $user1 = User::factory()->create(['name' => 'Test User 1']);
        
        // Create test activity
        activity('test')->causedBy($user1)->log('Test action');

        // Test API with user filter
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get('/api/admin/activity-logs?user_id=' . $user1->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'log_name',
                        'description',
                        'causer',
                        'properties',
                        'subject',
                        'timestamp',
                        'created_at'
                    ]
                ]
            ]);
    }
}

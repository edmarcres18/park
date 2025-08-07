<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use App\Models\User;
use App\Models\Plate;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

/**
 * Feature tests for Activity Log API
 */
class ActivityLogApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $attendant;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'attendant']);
        Role::create(['name' => 'user']);

        // Create users
        $this->admin = User::factory()->create(['name' => 'Admin User', 'status' => 'active']);
        $this->admin->assignRole('admin');

        $this->attendant = User::factory()->create(['name' => 'Attendant User', 'status' => 'active']);
        $this->attendant->assignRole('attendant');

        $this->regularUser = User::factory()->create(['name' => 'Regular User', 'status' => 'active']);
        $this->regularUser->assignRole('user');
    }

    /**
     * Test admin can access activity logs via API
     */
    public function test_admin_can_access_activity_logs_via_api(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'meta']);
    }

    /**
     * Test non-admin users cannot access activity logs via API
     */
    public function test_non_admin_users_cannot_access_activity_logs_via_api(): void
    {
        // Test attendant access
        Sanctum::actingAs($this->attendant, ['*']);

        $response = $this->getJson('/api/admin/activity-logs');

        $response->assertStatus(403);

        // Test regular user access
        Sanctum::actingAs($this->regularUser, ['*']);

        $response = $this->getJson('/api/admin/activity-logs');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated users are denied access
     */
    public function test_unauthenticated_users_are_denied_access(): void
    {
        $response = $this->getJson('/api/admin/activity-logs');

        $response->assertStatus(401);
    }

    /**
     * Test activity logs can be filtered by date range via API
     */
    public function test_activity_logs_can_be_filtered_by_date_range_via_api(): void
    {
        // Create activities through user action
        $plate = Plate::create(['number' => 'API-TEST', 'owner_name' => 'User', 'vehicle_type' => 'car']);
        $plate->update(['owner_name' => 'User Updated']);

        Sanctum::actingAs($this->admin, ['*']);

        // Filter by today's date
        $today = now()->format('Y-m-d');

        $response = $this->getJson('/api/admin/activity-logs?' . http_build_query([
            'date_from' => $today,
            'date_to' => $today
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));

        // Filter by tomorrow's date (should show no results)
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->getJson('/api/admin/activity-logs?' . http_build_query([
            'date_from' => $tomorrow,
            'date_to' => $tomorrow
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Test activity logs can be filtered by user via API
     */
    public function test_activity_logs_can_be_filtered_by_user_via_api(): void
    {
        // Create activity as admin
        Sanctum::actingAs($this->admin);

        $plate = Plate::create([
            'number' => 'ADMIN-123',
            'owner_name' => 'Admin Created',
            'vehicle_type' => 'car'
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        // Filter by admin user
        $response = $this->getJson('/api/admin/activity-logs?' . http_build_query([
            'user_id' => $this->admin->id
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Test activity logs can be filtered by model via API
     */
    public function test_activity_logs_can_be_filtered_by_model_via_api(): void
    {
        // Create a plate
        $plate = Plate::create([
            'number' => 'MODEL-123',
            'owner_name' => 'Model Test',
            'vehicle_type' => 'car',
        ]);

        // Log update and delete actions
        $plate->update(['owner_name' => 'Model Test Updated']);
        $plate->delete();

        Sanctum::actingAs($this->admin, ['*']);

        // Filter by plate model
        $response = $this->getJson('/api/admin/activity-logs?' . http_build_query([
            'log_name' => 'plate'
        ]));

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }
}

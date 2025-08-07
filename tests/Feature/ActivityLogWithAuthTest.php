<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Comprehensive feature tests for Activity Logs including auth events
 */
class ActivityLogWithAuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
     * Test login event creates activity log with IP and location
     */
    public function test_login_event_creates_activity_log_with_ip_and_location(): void
    {
        // Clear existing activities
        Activity::truncate();

        // Set a fake IP for testing
        $this->app['request']->server->set('REMOTE_ADDR', '8.8.8.8');

        // Fire login event
        event(new Login('web', $this->admin, false));

        // Process queued jobs
        $this->artisan('queue:work --once');

        // Check activity was created
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'User logged in',
            'subject_id' => $this->admin->id,
            'causer_id' => $this->admin->id,
            'event' => 'login'
        ]);

        $activity = Activity::where('log_name', 'auth')->first();
        $this->assertNotNull($activity);
        $this->assertNotNull($activity->properties['ip']);
        $this->assertNotNull($activity->properties['location']);
        $this->assertEquals('8.8.8.8', $activity->properties['ip']);
    }

    /**
     * Test logout event creates activity log
     */
    public function test_logout_event_creates_activity_log(): void
    {
        // Clear existing activities
        Activity::truncate();

        // Fire logout event
        event(new Logout('web', $this->admin));

        // Process queued jobs
        $this->artisan('queue:work --once');

        // Check activity was created
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'User logged out',
            'subject_id' => $this->admin->id,
            'causer_id' => $this->admin->id,
            'event' => 'logout'
        ]);
    }

    /**
     * Test CRUD operations create activity logs
     */
    public function test_crud_operations_create_activity_logs(): void
    {
        // Clear existing activities
        Activity::truncate();

        // Act as admin for CRUD operations
        $this->actingAs($this->admin);

        // Create operation
        $plate = Plate::create([
            'number' => 'TEST-123',
            'owner_name' => 'Test Owner',
            'vehicle_type' => 'car'
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'plate',
            'description' => 'created',
            'subject_id' => $plate->id,
            'causer_id' => $this->admin->id,
            'event' => 'created'
        ]);

        // Update operation
        $plate->update(['owner_name' => 'Updated Owner']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'plate',
            'description' => 'updated',
            'subject_id' => $plate->id,
            'causer_id' => $this->admin->id,
            'event' => 'updated'
        ]);

        // Delete operation
        $plate->delete();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'plate',
            'description' => 'deleted',
            'subject_id' => $plate->id,
            'causer_id' => $this->admin->id,
            'event' => 'deleted'
        ]);
    }

    /**
     * Test admin can view activity logs with auth events
     */
    public function test_admin_can_view_activity_logs_with_auth_events(): void
    {
        // Create some activities
        $this->actingAs($this->admin);

        // Create auth activity
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User logged in',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'event' => 'login', 
            'properties' => [
                'ip' => '192.168.1.1',
                'location' => ['city' => 'Test City', 'country' => 'Test Country', 'country_code' => 'TC']
            ]
        ]);

        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('User logged in');
        $response->assertSee('192.168.1.1');
        $response->assertSee('Test City');
        $response->assertSee('Test Country');
    }

    /**
     * Test activity logs can be filtered by auth events
     */
    public function test_activity_logs_can_be_filtered_by_auth_events(): void
    {
        $this->actingAs($this->admin);

        // Create auth activity
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User logged in',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'event' => 'login'
        ]);

        // Create plate activity
        $plate = Plate::factory()->create();

        // Filter by auth
        $response = $this->get(route('admin.activity-logs.index', ['model' => 'auth']));

        $response->assertStatus(200);
        $response->assertSee('User logged in');
        $response->assertDontSee('created'); // Should not see plate creation
    }

    /**
     * Test API returns activity logs with IP and location data
     */
    public function test_api_returns_activity_logs_with_ip_and_location_data(): void
    {
        $this->actingAs($this->admin);

        // Create auth activity
        Activity::create([
            'log_name' => 'auth',
            'description' => 'User logged in',
            'subject_type' => User::class,
            'subject_id' => $this->admin->id,
            'causer_type' => User::class,
            'causer_id' => $this->admin->id,
            'event' => 'login',
            'properties' => [
                'ip' => '10.0.0.1',
                'location' => ['city' => 'API City', 'country' => 'API Country']
            ]
        ]);

        $response = $this->getJson('/api/admin/activity-logs');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'log_name',
                    'description',
                    'ip',
                    'location',
                    'created_at'
                ]
            ]
        ]);

        $responseData = $response->json('data');
        $authActivity = collect($responseData)->firstWhere('log_name', 'auth');
        
        $this->assertNotNull($authActivity);
        $this->assertEquals('10.0.0.1', $authActivity['ip']);
        $this->assertEquals('API City', $authActivity['location']['city']);
    }

    /**
     * Test non-admin users cannot access activity logs page
     */
    public function test_non_admin_users_cannot_access_activity_logs_page(): void
    {
        // Test attendant access
        $response = $this->actingAs($this->attendant)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(403);

        // Test regular user access
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(403);
    }

    /**
     * Test non-admin users cannot access activity logs API
     */
    public function test_non_admin_users_cannot_access_activity_logs_api(): void
    {
        // Test attendant access
        $response = $this->actingAs($this->attendant)
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(403);

        // Test regular user access
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/admin/activity-logs');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated users are redirected
     */
    public function test_unauthenticated_users_are_redirected_for_web(): void
    {
        $response = $this->get(route('admin.activity-logs.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test unauthenticated API access is denied
     */
    public function test_unauthenticated_api_access_is_denied(): void
    {
        $response = $this->getJson('/api/admin/activity-logs');
        $response->assertStatus(401);
    }

    /**
     * Test activity log changes are displayed correctly
     */
    public function test_activity_log_changes_are_displayed_correctly(): void
    {
        $this->actingAs($this->admin);

        $plate = Plate::create([
            'number' => 'CHANGE-123',
            'owner_name' => 'Original Owner',
            'vehicle_type' => 'car'
        ]);

        $plate->update(['owner_name' => 'New Owner']);

        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Original Owner');
        $response->assertSee('New Owner');
    }

    /**
     * Test activity logs pagination works
     */
    public function test_activity_logs_pagination_works(): void
    {
        $this->actingAs($this->admin);

        // Create many activities
        for ($i = 1; $i <= 30; $i++) {
            Activity::create([
                'log_name' => 'test',
                'description' => "Test activity {$i}",
                'causer_type' => User::class,
                'causer_id' => $this->admin->id,
                'event' => 'test'
            ]);
        }

        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('activities');
        
        $activities = $response->viewData('activities');
        $this->assertTrue($activities->hasPages());
        $this->assertLessThanOrEqual(15, $activities->count()); // Default per page
    }

    /**
     * Test only dirty changes are logged
     */
    public function test_only_dirty_changes_are_logged(): void
    {
        Activity::truncate();
        
        $this->actingAs($this->admin);

        $plate = Plate::create([
            'number' => 'DIRTY-123',
            'owner_name' => 'Test Owner',
            'vehicle_type' => 'car'
        ]);

        // Count activities after creation
        $activitiesAfterCreate = Activity::count();

        // Update with same values (no dirty changes)
        $plate->update([
            'owner_name' => 'Test Owner', // Same value
            'vehicle_type' => 'car' // Same value
        ]);

        // Count should remain the same (no new activity for non-dirty update)
        $this->assertEquals($activitiesAfterCreate, Activity::count());

        // Update with different values (dirty changes)
        $plate->update(['owner_name' => 'Different Owner']);

        // Count should increase (new activity for dirty update)
        $this->assertEquals($activitiesAfterCreate + 1, Activity::count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature tests for Activity Log Controller
 */
class ActivityLogControllerTest extends TestCase
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
     * Test admin can access activity logs page
     */
    public function test_admin_can_access_activity_logs_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('activity_logs.index');
        $response->assertViewHas(['activities', 'users', 'models']);
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
     * Test unauthenticated users are redirected to login
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test activity logs are displayed correctly
     */
    public function test_activity_logs_are_displayed_correctly(): void
    {
        // Create a plate to generate activity
        $plate = Plate::create([
            'number' => 'ABC-123',
            'owner_name' => 'John Doe',
            'vehicle_type' => 'car'
        ]);

        // Update the plate to generate another activity
        $plate->update(['owner_name' => 'Jane Doe']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Activity Logs');
        $response->assertSee('ABC-123');
        $response->assertSee('John Doe');
        $response->assertSee('Jane Doe');
    }

    /**
     * Test activity logs can be filtered by date range
     */
    public function test_activity_logs_can_be_filtered_by_date_range(): void
    {
        // Create a plate today
        $plate = Plate::create([
            'number' => 'TODAY-123',
            'owner_name' => 'Today User',
            'vehicle_type' => 'car'
        ]);

        // Filter by today's date
        $today = now()->format('Y-m-d');
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'date_from' => $today,
                'date_to' => $today
            ]));

        $response->assertStatus(200);
        $response->assertSee('TODAY-123');

        // Filter by future date (should show no results)
        $future = now()->addDays(1)->format('Y-m-d');
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'date_from' => $future,
                'date_to' => $future
            ]));

        $response->assertStatus(200);
        $response->assertDontSee('TODAY-123');
    }

    /**
     * Test activity logs can be filtered by user
     */
    public function test_activity_logs_can_be_filtered_by_user(): void
    {
        // Create activity as admin
        $this->actingAs($this->admin);
        $plateByAdmin = Plate::create([
            'number' => 'ADMIN-123',
            'owner_name' => 'Admin Created',
            'vehicle_type' => 'car'
        ]);

        // Create activity as attendant  
        $this->actingAs($this->attendant);
        $plateByAttendant = Plate::create([
            'number' => 'ATTENDANT-123',
            'owner_name' => 'Attendant Created',
            'vehicle_type' => 'car'
        ]);

        // Filter by admin user
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'user_id' => $this->admin->id
            ]));

        $response->assertStatus(200);
        $response->assertSee('ADMIN-123');
        $response->assertDontSee('ATTENDANT-123');
    }

    /**
     * Test activity logs can be filtered by model type
     */
    public function test_activity_logs_can_be_filtered_by_model_type(): void
    {
        // Create different model activities
        $plate = Plate::create([
            'number' => 'FILTER-123',
            'owner_name' => 'Filter Test',
            'vehicle_type' => 'car'
        ]);

        $user = User::factory()->create(['name' => 'Filter User']);

        // Filter by plate model
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'model' => 'plate'
            ]));

        $response->assertStatus(200);
        $response->assertSee('FILTER-123');

        // Filter by user model
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'model' => 'user'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Filter User');
    }

    /**
     * Test activity logs pagination works correctly
     */
    public function test_activity_logs_pagination_works_correctly(): void
    {
        // Create multiple plates to generate many activities
        for ($i = 1; $i <= 25; $i++) {
            Plate::create([
                'number' => "PLATE-{$i}",
                'owner_name' => "Owner {$i}",
                'vehicle_type' => 'car'
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('activities');
        
        $activities = $response->viewData('activities');
        $this->assertLessThanOrEqual(15, $activities->count()); // Default per page
        $this->assertTrue($activities->hasPages());
    }

    /**
     * Test invalid date range validation
     */
    public function test_invalid_date_range_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'date_from' => '2024-12-31', 
                'date_to' => '2024-01-01' // date_to is before date_from
            ]));

        $response->assertSessionHasErrors('date_to');
    }

    /**
     * Test invalid user_id validation
     */
    public function test_invalid_user_id_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'user_id' => 99999 // Non-existent user ID
            ]));

        $response->assertSessionHasErrors('user_id');
    }

    /**
     * Test activity log shows correct event types
     */
    public function test_activity_log_shows_correct_event_types(): void
    {
        $plate = Plate::create([
            'number' => 'EVENT-123',
            'owner_name' => 'Event Test',
            'vehicle_type' => 'car'
        ]);

        $plate->update(['owner_name' => 'Updated Event Test']);
        $plate->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Created');
        $response->assertSee('Updated'); 
        $response->assertSee('Deleted');
    }

    /**
     * Test view renders with empty activity logs
     */
    public function test_view_renders_with_empty_activity_logs(): void
    {
        // Clear any existing activities
        Activity::truncate();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('No Activity Logs Found');
    }
}

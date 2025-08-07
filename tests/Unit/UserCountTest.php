<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_count_methods_return_correct_counts()
    {
        // Create roles
        $attendantRole = Role::create(['name' => 'attendant']);
        $adminRole = Role::create(['name' => 'admin']);

        // Create users with different statuses
        $activeUser = User::factory()->create(['status' => 'active']);
        $activeUser->assignRole($attendantRole);

        $pendingUser = User::factory()->create(['status' => 'pending']);
        $pendingUser->assignRole($attendantRole);

        $rejectedUser = User::factory()->create(['status' => 'rejected']);
        $rejectedUser->assignRole($attendantRole);

        // Create an admin user (should not be counted)
        $adminUser = User::factory()->create(['status' => 'active']);
        $adminUser->assignRole($adminRole);

        // Test individual count methods
        $this->assertEquals(1, User::getActiveAttendantsCount());
        $this->assertEquals(1, User::getPendingAttendantsCount());
        $this->assertEquals(1, User::getRejectedAttendantsCount());

        // Test the combined method
        $counts = User::getUserCountsByStatus();
        $this->assertEquals(1, $counts['active']);
        $this->assertEquals(1, $counts['pending']);
        $this->assertEquals(1, $counts['rejected']);
    }

    public function test_user_count_methods_return_zero_when_no_users()
    {
        // Test with no users
        $this->assertEquals(0, User::getActiveAttendantsCount());
        $this->assertEquals(0, User::getPendingAttendantsCount());
        $this->assertEquals(0, User::getRejectedAttendantsCount());

        $counts = User::getUserCountsByStatus();
        $this->assertEquals(0, $counts['active']);
        $this->assertEquals(0, $counts['pending']);
        $this->assertEquals(0, $counts['rejected']);
    }
}

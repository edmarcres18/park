<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;

class UserDeletionTest extends TestCase
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
    public function admin_can_delete_attendant_user()
    {
        // Create admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create attendant user to delete
        $attendant = User::factory()->create([
            'status' => 'active'
        ]);
        $attendant->assignRole('attendant');

        $this->actingAs($admin)
            ->delete(route('admin.users.delete', $attendant))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $attendant->id]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->delete(route('admin.users.delete', $admin))
            ->assertRedirect()
            ->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /** @test */
    public function admin_cannot_delete_other_admin_users()
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('admin');

        $this->actingAs($admin1)
            ->delete(route('admin.users.delete', $admin2))
            ->assertRedirect()
            ->assertSessionHas('error', 'Admin users cannot be deleted through this interface.');

        $this->assertDatabaseHas('users', ['id' => $admin2->id]);
    }

    /** @test */
    public function admin_can_bulk_delete_attendant_users()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $attendants = User::factory()->count(3)->create(['status' => 'active']);
        foreach ($attendants as $attendant) {
            $attendant->assignRole('attendant');
        }

        $userIds = $attendants->pluck('id')->toArray();

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-delete'), [
                'user_ids' => $userIds
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($userIds as $id) {
            $this->assertDatabaseMissing('users', ['id' => $id]);
        }
    }

    /** @test */
    public function bulk_delete_prevents_self_deletion()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $attendant = User::factory()->create(['status' => 'active']);
        $attendant->assignRole('attendant');

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-delete'), [
                'user_ids' => [$admin->id, $attendant->id]
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Admin should still exist
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Attendant should be deleted
        $this->assertDatabaseMissing('users', ['id' => $attendant->id]);
    }

    /** @test */
    public function deletion_removes_related_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $attendant = User::factory()->create(['status' => 'active']);
        $attendant->assignRole('attendant');

        // Create some notifications for the attendant
        $attendant->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => json_encode(['message' => 'test']),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.delete', $attendant))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Check that user and related data are deleted
        $this->assertDatabaseMissing('users', ['id' => $attendant->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $attendant->id]);
        $this->assertDatabaseMissing('model_has_roles', ['model_id' => $attendant->id]);
    }

    /** @test */
    public function non_admin_users_cannot_access_delete_routes()
    {
        $attendant = User::factory()->create();
        $attendant->assignRole('attendant');

        $userToDelete = User::factory()->create();
        $userToDelete->assignRole('attendant');

        $this->actingAs($attendant)
            ->delete(route('admin.users.delete', $userToDelete))
            ->assertStatus(403);

        $this->actingAs($attendant)
            ->post(route('admin.users.bulk-delete'), ['user_ids' => [$userToDelete->id]])
            ->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_delete_routes()
    {
        $userToDelete = User::factory()->create();
        $userToDelete->assignRole('attendant');

        $this->delete(route('admin.users.delete', $userToDelete))
            ->assertRedirect(route('login'));

        $this->post(route('admin.users.bulk-delete'), ['user_ids' => [$userToDelete->id]])
            ->assertRedirect(route('login'));
    }
}

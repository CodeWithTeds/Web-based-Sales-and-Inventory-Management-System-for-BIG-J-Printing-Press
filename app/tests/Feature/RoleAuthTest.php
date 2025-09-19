<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_staff_can_access_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
        ]);

        $response = $this->actingAs($user)->get('/staff/dashboard');

        $response->assertStatus(200);
    }

    public function test_driver_can_access_driver_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'driver',
        ]);

        $response = $this->actingAs($user)->get('/driver/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/staff/dashboard');

        $response->assertStatus(403);
    }

    public function test_client_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }
}
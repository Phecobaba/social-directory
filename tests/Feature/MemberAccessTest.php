<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_members_index(): void
    {
        $response = $this->get('/members');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_members_create_page(): void
    {
        $response = $this->get('/members/create');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_members_store(): void
    {
        $response = $this->post('/members', []);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_members_edit_page(): void
    {
        $member = Member::factory()->create();

        $response = $this->get("/members/{$member->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_members_update(): void
    {
        $member = Member::factory()->create();

        $response = $this->put("/members/{$member->id}", []);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_members_destroy(): void
    {
        $member = Member::factory()->create();

        $response = $this->delete("/members/{$member->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_members_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/members');

        $response->assertOk();
    }

    public function test_authenticated_admin_can_access_members_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/members/create');

        $response->assertOk();
    }

    public function test_authenticated_admin_can_access_members_edit_page(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();

        $response = $this->actingAs($user)->get("/members/{$member->id}/edit");

        $response->assertOk();
    }
}

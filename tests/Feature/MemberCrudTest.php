<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_member(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/members', [
            'full_name' => 'Amina Okafor',
            'phone_number' => '08012345678',
            'address' => '12 Broad Street, Lagos',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('members', [
            'full_name' => 'Amina Okafor',
            'phone_number' => '08012345678',
            'address' => '12 Broad Street, Lagos',
        ]);
    }

    public function test_admin_can_edit_a_member(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();

        $response = $this->actingAs($user)->get("/members/{$member->id}/edit");

        $response->assertOk();
        $response->assertSee($member->full_name);
    }

    public function test_admin_can_update_a_member(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create([
            'full_name' => 'Old Member Name',
        ]);

        $response = $this->actingAs($user)->put("/members/{$member->id}", [
            'full_name' => 'Updated Member Name',
            'phone_number' => $member->phone_number,
            'address' => $member->address,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Updated Member Name',
        ]);
    }

    public function test_admin_can_delete_a_member(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create();

        $response = $this->actingAs($user)->delete("/members/{$member->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
    }
}

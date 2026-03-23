<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Admin Login')
            ->assertSee('Forgot password?');
    }

    public function test_admin_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'name' => 'clubadmin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'name' => 'clubadmin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'login' => $user->name,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Members')
            ->assertSee('New Members This Month')
            ->assertDontSee('Member Growth Trend')
            ->assertDontSee('Data Completeness');
    }

    public function test_admin_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot Password');
    }

    public function test_admin_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_admin_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'login' => $user->email,
            'password' => 'new-secure-password',
        ])->assertRedirect(route('dashboard'));
    }
}

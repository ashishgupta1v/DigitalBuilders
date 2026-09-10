<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAuthenticationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_cleanly(): void
    {
        $response = $this->get('/crm/login');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Crm/Login'));
    }

    public function test_login_requires_credentials(): void
    {
        $response = $this->postJson('/crm/login', [
            'email'    => '',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_non_admin_users(): void
    {
        $user = User::factory()->create([
            'email'    => 'regularuser@example.com',
            'password' => Hash::make('Secret1234!'),
            'is_admin' => false,
        ]);

        $response = $this->postJson('/crm/login', [
            'email'    => 'regularuser@example.com',
            'password' => 'Secret1234!',
        ]);

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_login_detects_must_change_password_and_flags_first_time(): void
    {
        $founder = User::factory()->create([
            'email'                => 'ashishgupta1v@gmail.com',
            'password'             => Hash::make('DB-Temp2026!'),
            'is_admin'             => true,
            'must_change_password' => true,
        ]);

        $response = $this->postJson('/crm/login', [
            'email'    => 'ashishgupta1v@gmail.com',
            'password' => 'DB-Temp2026!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'             => false,
            'first_time_required' => true,
            'email'               => 'ashishgupta1v@gmail.com',
        ]);
        $this->assertGuest();
    }

    public function test_first_time_password_update_activates_account(): void
    {
        $founder = User::factory()->create([
            'email'                => 'ashishgupta1v@gmail.com',
            'password'             => Hash::make('DB-Temp2026!'),
            'is_admin'             => true,
            'must_change_password' => true,
        ]);

        $response = $this->postJson('/crm/password/first-time-update', [
            'email'                 => 'ashishgupta1v@gmail.com',
            'temp_password'         => 'DB-Temp2026!',
            'password'              => 'NewSecureFounderPass#2026',
            'password_confirmation' => 'NewSecureFounderPass#2026',
            'security_question'     => 'What is your primary software architecture focus?',
            'security_answer'       => 'digital builders',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $founder->refresh();
        $this->assertFalse($founder->must_change_password);
        $this->assertTrue(Hash::check('NewSecureFounderPass#2026', $founder->password));
        $this->assertEquals('What is your primary software architecture focus?', $founder->security_question);
        $this->assertTrue($founder->verifySecurityAnswer('Digital Builders'));
        $this->assertAuthenticatedAs($founder);
    }

    public function test_first_time_password_update_rejects_wrong_temp_password(): void
    {
        User::factory()->create([
            'email'                => 'ashishgupta1v@gmail.com',
            'password'             => Hash::make('DB-Temp2026!'),
            'is_admin'             => true,
            'must_change_password' => true,
        ]);

        $response = $this->postJson('/crm/password/first-time-update', [
            'email'                 => 'ashishgupta1v@gmail.com',
            'temp_password'         => 'WrongTemp123',
            'password'              => 'NewSecureFounderPass#2026',
            'password_confirmation' => 'NewSecureFounderPass#2026',
            'security_question'     => 'What is your primary focus?',
            'security_answer'       => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['temp_password']);
    }

    public function test_get_security_question_returns_question_for_valid_admin(): void
    {
        User::factory()->create([
            'email'             => 'ashishgupta1v@gmail.com',
            'is_admin'          => true,
            'security_question' => 'What was the name of your first software product?',
            'security_answer'   => Hash::make('crm suite'),
        ]);

        $response = $this->postJson('/crm/password/question', [
            'email' => 'ashishgupta1v@gmail.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'question' => 'What was the name of your first software product?',
        ]);
    }

    public function test_get_security_question_returns_404_for_unknown_email(): void
    {
        $response = $this->postJson('/crm/password/question', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_reset_with_security_question_resets_password_and_authenticates(): void
    {
        $user = User::factory()->create([
            'email'             => 'ashishgupta1v@gmail.com',
            'password'          => Hash::make('OldPassword123!'),
            'is_admin'          => true,
            'security_question' => 'What city was your company originally founded in?',
            'security_answer'   => Hash::make('chandigarh'),
        ]);

        $response = $this->postJson('/crm/password/reset-question', [
            'email'                 => 'ashishgupta1v@gmail.com',
            'security_answer'       => 'Chandigarh', // test case-insensitivity
            'password'              => 'BrandNewMasterPass2026!',
            'password_confirmation' => 'BrandNewMasterPass2026!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewMasterPass2026!', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_with_security_question_rejects_wrong_answer(): void
    {
        User::factory()->create([
            'email'             => 'ashishgupta1v@gmail.com',
            'password'          => Hash::make('OldPassword123!'),
            'is_admin'          => true,
            'security_question' => 'What city was your company originally founded in?',
            'security_answer'   => Hash::make('chandigarh'),
        ]);

        $response = $this->postJson('/crm/password/reset-question', [
            'email'                 => 'ashishgupta1v@gmail.com',
            'security_answer'       => 'Wrong City',
            'password'              => 'BrandNewMasterPass2026!',
            'password_confirmation' => 'BrandNewMasterPass2026!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['security_answer']);
        $this->assertGuest();
    }

    public function test_authenticated_admin_can_update_password_in_cockpit(): void
    {
        $admin = User::factory()->create([
            'email'    => 'ashishgupta1v@gmail.com',
            'password' => Hash::make('CurrentSecret2026!'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->postJson('/crm/profile/password', [
            'current_password'      => 'CurrentSecret2026!',
            'password'              => 'SuperUpdatedPass#999',
            'password_confirmation' => 'SuperUpdatedPass#999',
            'security_question'     => 'What is your private master recovery phrase?',
            'security_answer'       => 'solopreneur builder',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $admin->refresh();
        $this->assertTrue(Hash::check('SuperUpdatedPass#999', $admin->password));
        $this->assertEquals('What is your private master recovery phrase?', $admin->security_question);
        $this->assertTrue($admin->verifySecurityAnswer('solopreneur builder'));
    }
}

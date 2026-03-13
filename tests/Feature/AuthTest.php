<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(AuthController::class)]
#[Group('auth')]
final class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_user(): void
    {
        // Arrange
        Mail::fake();

        // Act
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        Mail::assertQueued(WelcomeUserMail::class, function ($mail) {
            return $mail->user->email === 'test@example.com';
        });
    }

    #[Test]
    public function it_can_login_a_user(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    #[Test]
    public function it_cannot_login_with_invalid_credentials(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid login details',
            ]);
    }

    #[Test]
    public function it_can_access_user_endpoint_with_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Act
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    #[Test]
    public function it_can_logout_a_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Act
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        $this->assertCount(0, $user->tokens);
    }
}

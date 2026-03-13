<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(PostController::class)]
#[Group('posts')]
final class PostTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Post::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->getJson('/api/posts');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'title',
                        'slug',
                        'content',
                        'created_at',
                        'updated_at',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_can_show_a_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->getJson("/api/posts/{$post->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title);
    }

    #[Test]
    public function it_can_create_a_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $postData = [
            'title' => 'My First Post',
            'content' => 'This is the content of my first post.',
        ];

        // Act
        $response = $this->postJson('/api/posts', $postData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('data.title', $postData['title'])
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_can_update_own_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        // Act
        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.title', $updateData['title']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
        ]);
    }

    #[Test]
    public function it_cannot_update_others_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'title' => 'Sneaky Update',
        ];

        // Act
        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        // Assert
        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_delete_own_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        // Act
        $response = $this->deleteJson("/api/posts/{$post->id}");

        // Assert
        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    #[Test]
    public function it_cannot_delete_others_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->deleteJson("/api/posts/{$post->id}");

        // Assert
        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(PostController::class)]
#[Group('posts')]
#[Group('images')]
final class PostImageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_post_with_images(): void
    {
        // Arrange
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $postData = [
            'title' => 'Post with images',
            'content' => 'Content with images.',
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
            ],
        ];

        // Act
        $response = $this->postJson('/api/posts', $postData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('data.title', $postData['title'])
            ->assertJsonCount(2, 'data.images');

        $postId = $response->json('data.id');

        $this->assertDatabaseHas('posts', ['id' => $postId]);
        $this->assertDatabaseHas('post_images', ['post_id' => $postId]);
        $this->assertDatabaseCount('post_images', 2);

        foreach ($response->json('data.images') as $imageData) {
            $path = str_replace('/storage/', '', $imageData['url']);
            Storage::disk('public')->assertExists($path);
        }
    }

    #[Test]
    public function it_can_add_images_to_existing_post(): void
    {
        // Arrange
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'images' => [
                UploadedFile::fake()->image('new_image.png'),
            ],
        ];

        // Act
        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.images');

        $this->assertDatabaseCount('post_images', 1);
    }

    #[Test]
    public function it_deletes_images_when_post_is_deleted(): void
    {
        // Arrange
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $postData = [
            'title' => 'Post to delete',
            'content' => 'Content to delete.',
            'images' => [
                UploadedFile::fake()->image('delete_me.jpg'),
            ],
        ];

        // Create with images
        $response = $this->postJson('/api/posts', $postData);
        $postId = $response->json('data.id');
        $imagePath = str_replace('/storage/', '', $response->json('data.images.0.url'));

        Storage::disk('public')->assertExists($imagePath);

        // Act
        $deleteResponse = $this->deleteJson("/api/posts/{$postId}");

        // Assert
        $deleteResponse->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
        $this->assertDatabaseMissing('post_images', ['post_id' => $postId]);
        Storage::disk('public')->assertMissing($imagePath);
    }
}

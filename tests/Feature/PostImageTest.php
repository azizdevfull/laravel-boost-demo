<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_post_with_images(): void
    {
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

        $response = $this->postJson('/api/posts', $postData);

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

    public function test_can_add_images_to_existing_post(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'images' => [
                UploadedFile::fake()->image('new_image.png'),
            ],
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.images');

        $this->assertDatabaseCount('post_images', 1);
    }

    public function test_images_are_deleted_when_post_is_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

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

        // Delete post
        $deleteResponse = $this->deleteJson("/api/posts/{$postId}");
        $deleteResponse->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
        $this->assertDatabaseMissing('post_images', ['post_id' => $postId]);
        Storage::disk('public')->assertMissing($imagePath);
    }
}

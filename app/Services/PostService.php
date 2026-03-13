<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Post\CreatePostDTO;
use App\DTOs\Post\UpdatePostDTO;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository
    ) {}

    /**
     * Get all posts.
     *
     * @return Collection<int, Post>
     */
    public function getAllPosts(): Collection
    {
        return $this->postRepository->all();
    }

    /**
     * Get a post by ID.
     */
    public function getPost(int $id): Post
    {
        $post = $this->postRepository->find($id);

        if (! $post) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Post with ID {$id} not found.");
        }

        return $post;
    }

    /**
     * Create a new post.
     */
    public function createPost(CreatePostDTO $dto): Post
    {
        return $this->postRepository->create($dto->toArray());
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, UpdatePostDTO $dto): Post
    {
        return $this->postRepository->update($post, $dto->toArray());
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post);
    }
}

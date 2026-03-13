<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    /**
     * Get all posts.
     *
     * @return Collection<int, Post>
     */
    public function all(): Collection;

    /**
     * Find a post by ID.
     */
    public function find(int $id): ?Post;

    /**
     * Create a new post.
     */
    public function create(array $data): Post;

    /**
     * Update an existing post.
     */
    public function update(Post $post, array $data): Post;

    /**
     * Delete a post.
     */
    public function delete(Post $post): bool;
}

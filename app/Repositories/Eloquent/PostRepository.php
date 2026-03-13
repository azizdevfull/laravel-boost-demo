<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function __construct(
        private readonly Post $model
    ) {}

    public function all(): Collection
    {
        return $this->model->latest()->get();
    }

    public function find(int $id): ?Post
    {
        return $this->model->find($id);
    }

    public function create(array $data): Post
    {
        return $this->model->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }
}

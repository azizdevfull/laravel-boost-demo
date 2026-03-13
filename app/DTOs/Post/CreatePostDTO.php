<?php

declare(strict_types=1);

namespace App\DTOs\Post;

use App\Http\Requests\Post\StorePostRequest;
use Illuminate\Support\Str;

readonly class CreatePostDTO
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $slug,
        public string $content,
    ) {}

    public static function fromRequest(StorePostRequest $request): self
    {
        return new self(
            userId: $request->user()->id,
            title: $request->validated('title'),
            slug: Str::slug($request->validated('title')),
            content: $request->validated('content'),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
        ];
    }
}

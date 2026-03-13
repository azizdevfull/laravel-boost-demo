<?php

declare(strict_types=1);

namespace App\DTOs\Post;

use App\Http\Requests\Post\StorePostRequest;
use Illuminate\Support\Str;

readonly class CreatePostDTO
{
    /**
     * @param  array<\Illuminate\Http\UploadedFile>|null  $images
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string $slug,
        public string $content,
        public ?array $images = null,
    ) {}

    public static function fromRequest(StorePostRequest $request): self
    {
        return new self(
            userId: $request->user()->id,
            title: $request->validated('title'),
            slug: Str::slug($request->validated('title')),
            content: $request->validated('content'),
            images: $request->file('images'),
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

<?php

declare(strict_types=1);

namespace App\DTOs\Post;

use App\Http\Requests\Post\UpdatePostRequest;
use Illuminate\Support\Str;

readonly class UpdatePostDTO
{
    /**
     * @param  array<\Illuminate\Http\UploadedFile>|null  $images
     */
    public function __construct(
        public ?string $title = null,
        public ?string $slug = null,
        public ?string $content = null,
        public ?array $images = null,
    ) {}

    public static function fromRequest(UpdatePostRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            slug: $request->has('title') ? Str::slug($request->validated('title')) : null,
            content: $request->validated('content'),
            images: $request->file('images'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
        ], fn ($value) => $value !== null);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Post $resource
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'content' => $this->resource->content,
            'images' => $this->resource->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->getUrl(),
            ]),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}

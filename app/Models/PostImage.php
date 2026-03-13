<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostImage extends Model
{
    /** @use HasFactory<\Database\Factories\PostImageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'path',
    ];

    /**
     * Get the post that owns the image.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Post, \App\Models\PostImage>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getUrl(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}

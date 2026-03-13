<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\Post\CreatePostDTO;
use App\DTOs\Post\UpdatePostDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $posts = $this->postService->getAllPosts();

        return PostResource::collection($posts->load(['user', 'images']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $dto = CreatePostDTO::fromRequest($request);
        $post = $this->postService->createPost($dto);

        return (new PostResource($post->load('images')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): PostResource
    {
        $post = $this->postService->getPost($id);

        return new PostResource($post->load(['user', 'images']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        Gate::authorize('update', $post);

        $dto = UpdatePostDTO::fromRequest($request);
        $updatedPost = $this->postService->updatePost($post, $dto);

        return new PostResource($updatedPost->load('images'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);

        $this->postService->deletePost($post);

        return response()->json(null, 204);
    }
}

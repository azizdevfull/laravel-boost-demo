<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {}

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findOrFailByEmail(string $email): User
    {
        return $this->model->where('email', $email)->firstOrFail();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }
}

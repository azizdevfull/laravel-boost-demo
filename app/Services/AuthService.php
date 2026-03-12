<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Auth\AuthResponseDTO;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Exceptions\InvalidCredentialsException;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function register(RegisterDTO $dto): AuthResponseDTO
    {
        $user = $this->userRepository->create($dto->toArray());

        Mail::to($user->email)->queue(new WelcomeUserMail($user));

        return $this->generateAuthResponse($user);
    }

    public function login(LoginDTO $dto): AuthResponseDTO
    {
        if (! Auth::attempt($dto->toArray())) {
            throw new InvalidCredentialsException;
        }

        $user = $this->userRepository->findOrFailByEmail($dto->email);

        return $this->generateAuthResponse($user);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function generateAuthResponse(User $user): AuthResponseDTO
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResponseDTO($token);
    }
}

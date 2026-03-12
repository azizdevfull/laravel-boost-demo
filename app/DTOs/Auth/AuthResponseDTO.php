<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

readonly class AuthResponseDTO
{
    public function __construct(
        public string $accessToken,
        public string $tokenType = 'Bearer',
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
        ];
    }
}

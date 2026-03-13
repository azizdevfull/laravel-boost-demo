<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('web')]
final class ExampleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_successful_response_for_the_homepage(): void
    {
        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
    }
}

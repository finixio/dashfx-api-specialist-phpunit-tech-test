<?php

declare(strict_types=1);

namespace App\Integrations\Domain\DTO;

class IntegrationRequest
{
    public function __construct(private array $userData)
    {
    }

    public function userData(): array
    {
        return $this->userData;
    }
}

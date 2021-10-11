<?php

declare(strict_types=1);

namespace App\Integrations\Domain\DTO;

class IntegrationResponse
{
    public function __construct(private bool $success, private ?string $message = null, private array $errors = [])
    {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function errors(): ?array
    {
        return $this->isSuccess()
            ? null
            : $this->errors;
    }

    public function redirectUrl(): ?string
    {
        return $this->isSuccess()
            ? $this->message
            : null;
    }

    public function errorMessage(): ?string
    {
        return !$this->isSuccess()
            ? $this->message
            : null;
    }
}

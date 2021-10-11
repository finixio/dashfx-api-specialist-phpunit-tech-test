<?php

declare(strict_types=1);

namespace App\Integrations\Domain;

use App\Integrations\Domain\DTO\IntegrationRequest;
use App\Integrations\Infrastructure\DashFx;
use GuzzleHttp\Client;

class IntegrationFactory
{
    public const DASHFX = 'dashfx';

    public function __construct(
        protected Client $client,
        protected IntegrationRequest $request
    ) {
    }

    public function create(string $integration): Integration
    {
        return match ($integration) {
            static::DASHFX => new DashFx($this->client, $this->request),
            default        => throw new \RuntimeException('Invalid Integration: '.$integration),
        };
    }
}

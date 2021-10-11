<?php

declare(strict_types=1);

namespace App\Integrations\Domain;

use App\Integrations\Domain\DTO\IntegrationRequest;
use App\Integrations\Domain\DTO\IntegrationResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;

abstract class Integration
{
    public function __construct(
        protected Client $client,
        protected IntegrationRequest $request,
    ) {
    }

    abstract public function url(): string;

    abstract protected function method(): string;

    abstract protected function payload(): array;

    abstract protected function integrationResponse(ResponseInterface $response): IntegrationResponse;

    final public function execute(): IntegrationResponse
    {
        try {
            $integrationResponse = $this->integrationResponse(
                $this->client->request(
                    $this->method(),
                    $this->url(),
                    $this->payload()
                )
            );
        } catch (ClientException|ServerException $ce) {
            $integrationResponse = $this->integrationResponse(
                $ce->getResponse()
            );
        }

        return $integrationResponse;
    }
}

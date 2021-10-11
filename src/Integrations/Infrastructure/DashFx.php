<?php

declare(strict_types=1);

namespace App\Integrations\Infrastructure;

use App\Integrations\Domain\DTO\IntegrationResponse;
use App\Integrations\Domain\Integration;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class DashFx extends Integration
{
    protected const TIMEOUT = 20; // seconds

    protected const PAYLOAD_FIELD = RequestOptions::JSON;
    protected const ENDPOINT      = '/fake-broker-api.php';

    public function url(): string
    {
        return ($_ENV['DASHFX_URL'] ?? '').self::ENDPOINT;
    }

    public function key(): string
    {
        return $_ENV['DASHFX_KEY'] ?? '';
    }

    protected function method(): string
    {
        return 'POST';
    }

    protected function payload(): array
    {
        $submitted = $this->request->userData();

        return [
            'timeout'               => static::TIMEOUT,
            RequestOptions::HEADERS => [
                'Content-Type'  => 'application/json',
                'Authorization' => $this->key(),
            ],
            static::PAYLOAD_FIELD => [
                // user variables
                'firstName' => $submitted['firstName'] ?? '',
                'lastName'  => $submitted['lastName'] ?? '',
                'email'     => $submitted['email'] ?? '',
                'phone'     => $submitted['phone'] ?? '',
                'country'   => $submitted['country'] ?? '',
                'ip'        => $submitted['ip'] ?? '',
            ],
        ];
    }

    protected function integrationResponse(ResponseInterface $response): IntegrationResponse
    {
        $responseArr = json_decode((string) $response->getBody(), true);

        if (200 === $response->getStatusCode()) {
            return new IntegrationResponse(
                success: true,
                message: $this->getDepositUrlFromResponseArr($responseArr)
            );
        }

        // otherwise return error
        return new IntegrationResponse(
            success: false,
            message: $responseArr['error'] ?? '',
            errors: $responseArr['fields'] ?? []
        );
    }

    protected function getDepositUrlFromResponseArr(array $responseArr): ?string
    {
        return $responseArr['depositUrl'] ?? null;
    }
}

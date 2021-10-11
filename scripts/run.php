<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Integrations\Domain\DTO\IntegrationRequest;
use App\Integrations\Domain\IntegrationFactory;
use GuzzleHttp\Client;

/*
 * This script sets up dummy userData and has the DashFx integration submit a lead to the fake api.
 * NOTE: make sure the fake api is running with `make broker-up`
 *
 * In a perfect world these would be Dependency Injected into the IntegrationFactory, but I wanted to keep this light
 */

Dotenv\Dotenv::createMutable(__DIR__.'/..')->load();

// construct our guzzle client
$client = new Client();

// simulate some lead data
$integrationRequest = new IntegrationRequest([
    'firstName' => 'test',
    'lastName'  => 'test',
    'email'     => 'test@test.com',
    'phone'     => '+44789012345',
    'country'   => 'DE',
    'ip'        => '66.77.88.99',
]);

$integrationFactory = new IntegrationFactory($client, $integrationRequest);

$dashfx = $integrationFactory->create(IntegrationFactory::DASHFX);

printf("Sending a lead to the DashFx integration.\n\tURL     : %s\n\tPayload : %s\n\n", $dashfx->url(), json_encode($integrationRequest->userData()));
$response = $dashfx->execute();

if ($response->isSuccess()) {
    printf("Success!\nWe sent the lead to the dashfx fake api, the redirect url is: %s\n", $response->redirectUrl());
} else {
    printf("error :(\nThe API Responded with the error: %s\nFields affected were: %s\n", $response->errorMessage(), json_encode($response->errors()));
}

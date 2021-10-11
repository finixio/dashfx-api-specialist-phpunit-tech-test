<?php

declare(strict_types=1);

namespace Tests\Integrations\Infrastructure;

use App\Integrations\Domain\DTO\IntegrationRequest;
use App\Integrations\Domain\DTO\IntegrationResponse;
use App\Integrations\Domain\IntegrationFactory;
use App\Integrations\Infrastructure\DashFx;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DashFxTest extends TestCase
{
    use ProphecyTrait;
    protected IntegrationFactory $integrationFactory;

    public function test__factory(): void
    {
        $this->configureTest();

        $integration = $this->integrationFactory->create(IntegrationFactory::DASHFX);
        $this->assertEquals(DashFx::class, get_class($integration));
    }

    public function test__key(): void
    {
        $this->configureTest();
        $integration = $this->integrationFactory->create(IntegrationFactory::DASHFX);
        $this->assertEquals('s3cr3tk3y', $integration->key());
    }

    /** please complete the following tests */
    public function test__url(): void
    {
        $this->markTestIncomplete();
        //@todo 1. Create a DASHFX integration from the factory and check that the url is 'http://127.0.0.1:10101/fake-broker-api.php'
    }

    public function test__execute(): void
    {
        $this->markTestIncomplete();
        //@todo 2. Create a DASHFX integration from the factory and ensure the response is an IntegrationResponse class

        //@todo 3. On a successful execution ensure the response has a redirect url that matches "https://google.com/foo?userId=23434343"

        //@todo 4. On failure ensure the error message is "This user already exists in our system" Hint: you will need to mock the failure.
    }

    private function configureTest(bool $execute = false): void
    {
        $this->setFakeEnv();
        $clientProphecy     = $this->mockGuzzleClient($execute);
        $integrationRequest = $this->getIntegrationRequest();

        $this->integrationFactory = new IntegrationFactory($clientProphecy->reveal(), $integrationRequest);
    }

    private function setFakeEnv(): void
    {
        $_ENV['DASHFX_URL'] = 'http://127.0.0.1:10101';
        $_ENV['DASHFX_KEY'] = 's3cr3tk3y';
    }

    private function getIntegrationRequest(): IntegrationRequest
    {
        return new IntegrationRequest([
            'firstName' => 'test',
            'lastName'  => 'test',
            'email'     => 'test@test.com',
            'phone'     => '+44789012345',
            'country'   => 'DE',
            'ip'        => '66.77.88.99',
        ]);
    }

    private function mockGuzzleClient(bool $execute)
    {
        $clientProphecy = $this->prophesize(Client::class);

        //Mock the Guzzle response object
        $statusCode = 200;
        $response   = $this->mockGuzzleResponse($statusCode, $execute);
        //Mock post
        $clientProphecy
            ->request(Argument::any(), Argument::any(), Argument::any())
            ->willReturn($response)
            ->shouldBeCalledTimes($execute ? 1 : 0);

        return $clientProphecy;
    }

    private function mockGuzzleResponse($statusCode, bool $execute)
    {
        $responseProphecy = $this->prophesize(Response::class);

        //Mock getStatusCode
        $responseProphecy
            ->getStatusCode()
            ->willReturn($statusCode)
            ->shouldBeCalledTimes($execute ? 1 : 0);

        //Mock getBody
        $responseProphecy
            ->getBody()
            ->willReturn($this->getIntegrationResponse())
            ->shouldBeCalledTimes($execute ? 1 : 0);

        return $responseProphecy;
    }

    private function getIntegrationResponse()
    {
        $response = <<<EOF
{
    "success": "The user test test using email test@test.com has been signed up to the broker.",
    "depositUrl": "https://google.com/foo?userId=23434343"
}
EOF;

        return $response;
    }
}

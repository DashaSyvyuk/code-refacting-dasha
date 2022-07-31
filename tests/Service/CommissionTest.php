<?php

declare(strict_types=1);

use App\Client\GuzzleClient;
use App\Exception\ExchangeRateApiException;
use App\Service\Commission;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class CommissionTest extends MockeryTestCase
{
    const BIN_API_URL = 'http://bin-api-url';
    const EXCHANGE_RATE_API_URL = 'http://exchange-rate-api-url';
    const EXCHANGE_RATE_API_KEY = 'apikey';

    private $client;

    public function __construct()
    {
        $this->client = \Mockery::mock(GuzzleClient::class);

        parent::__construct();
    }

    protected function setUp(): void
    {
        $this->service = new Commission(
            $this->client,
            self::BIN_API_URL,
            self::EXCHANGE_RATE_API_URL,
            self::EXCHANGE_RATE_API_KEY
        );

        parent::setUp();
    }

    public function test_calculate_successful()
    {
        $data = '{"bin":"516793","amount":"50.00","currency":"USD"}';

        $this->client->shouldReceive('get')->andReturn($this->getMockedExchangeRateApiSuccessfulResponse())->once();

        $this->client->shouldReceive('get')->andReturn($this->getMockedBinApiSuccessfulResponse())->once();

        $result = $this->service->calculate($data);

        $this->assertEquals(0.49, $result);
    }

    public function test_calculate_should_throw_exception()
    {
        $data = '{"bin":"516793","amount":"50.00","currency":"USD"}';

        $this->client->shouldReceive('get')->andReturn($this->getMockedExchangeRateApiErrorResponse())->once();

        $this->client->shouldReceive('get')->andReturn($this->getMockedBinApiSuccessfulResponse())->once();

        $this->expectException(ExchangeRateApiException::class);

        $this->service->calculate($data);
    }

    private function getMockedBinApiSuccessfulResponse(): array
    {
        return include __DIR__ . '/../Fixtures/bin_api_successful_response.php';
    }

    private function getMockedExchangeRateApiSuccessfulResponse(): array
    {
        return include __DIR__ . '/../Fixtures/exchange_rate_api_successful_response.php';
    }

    private function getMockedExchangeRateApiErrorResponse(): array
    {
        return include __DIR__ . '/../Fixtures/exchange_rate_api_error_response.php';
    }
}

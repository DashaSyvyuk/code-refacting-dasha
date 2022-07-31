<?php

namespace App\Service;

use App\Client\GuzzleClient;
use App\Exception\BinApiException;
use App\Exception\ExchangeRateApiException;
use GuzzleHttp\Exception\GuzzleException;

class Commission
{
    private GuzzleClient $client;

    private string $binApiUrl;

    private string $exchangeRateApiUrl;

    private string $apiKey;

    public function __construct($client, $binApiUrl, $exchangeRateApiUrl, $apiKey)
    {
        $this->client = $client;
        $this->binApiUrl = $binApiUrl;
        $this->exchangeRateApiUrl = $exchangeRateApiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @throws BinApiException
     * @throws ExchangeRateApiException
     */
    public function calculate(string $row): float
    {
        $exchangeRates = $this->getExchangeRates();

        [$bin, $amount, $currency] = array_values(json_decode($row, true));

        $countryCode = $this->getCountryCodeByBin($bin);

        $rate = $exchangeRates['rates'][$currency];

        $amountFixed = ($currency == 'EUR' or $rate == 0) ? $amount : $amount / $rate;

        return ceil($amountFixed * ($this->isEu($countryCode) ? 0.01 : 0.02) * 100) / 100;
    }

    /**
     * @param string $bin
     * @return string|null
     * @throws BinApiException
     */
    private function getCountryCodeByBin(string $bin): ?string
    {
        try {
            $result = $this->client->get($this->binApiUrl . $bin);

            if (!array_key_exists('country', $result)) {
                throw new BinApiException('We don\'t find information about country');
            }

            return $result['country']['alpha2'];
        } catch (GuzzleException $e) {
            throw new BinApiException('There were errors while we connect to bin API');
        }
    }

    /**
     * @return array
     * @throws ExchangeRateApiException
     */
    private function getExchangeRates(): array
    {
        try {
            $result = $this->client->get($this->exchangeRateApiUrl, [
                'apikey' => $this->apiKey
            ]);

            if (isset($result['error'])) {
                throw new ExchangeRateApiException($result['error']['info']);
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new ExchangeRateApiException('There were errors while we connect to Exchange Rate API');
        }
    }

    /**
     * @param string $country
     * @return bool
     */
    private function isEu(string $country): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
            'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL',
            'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        return in_array($country, $euCountries);
    }
}

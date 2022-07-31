<?php

namespace App\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleClient
{
    private Client $client;

    public function __construct() {
        $this->client = new Client();
    }

    /**
     * @param string $url
     * @param array $headers
     * @return array
     * @throws GuzzleException
     */
    public function get(string $url, array $headers = []): array
    {
        $response = $this->client->get($url, [
            'headers' => $headers
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}

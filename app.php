<?php

use App\Client\GuzzleClient;
use App\Service\Commission;

require __DIR__.'/vendor/autoload.php';

$config = include __DIR__ . '/config.php';

$commission = new Commission(
    new GuzzleClient(),
    $config['bin_api_url'],
    $config['exchange_rate_api_url'],
    $config['exchange_rate_api_key']
);

foreach (explode("\n", file_get_contents($argv[1])) as $row) {
    if (empty($row)) break;

    echo $commission->calculate($row);
    print "\n";
}

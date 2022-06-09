<?php

declare(strict_types=1);

use App\Commands\RemoveExpiredPasscodes;
use App\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require __DIR__ . '/bootstrap.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load(__DIR__ . '/config/services.yaml');
$containerBuilder->compile(true);

if (!isset($argv)) {
    Logger::error('$argv has not been specified');
    exit;
}

try {
    switch ($argv[1]) {
        case 'expiredPasscodesRemover':
            $removeExpiredPasscodes = $containerBuilder->get(RemoveExpiredPasscodes::class);
            $removeExpiredPasscodes->execute();
            break;
        default:
            throw new Exception('run parameter not specified');
    }
} catch (\Exception $e) {
    Logger::error($e->getMessage());
}

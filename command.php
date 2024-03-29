<?php

declare(strict_types=1);

use App\Commands\AddBooking;
use App\Commands\ConsumeQueue;
use App\Commands\RemoveDuplicates;
use App\Commands\RemoveExpiredPasscodes;
use App\Commands\RemovePasscode;
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
    $command = match ($argv[1]) {
        'expiredPasscodesRemover' => $containerBuilder->get(RemoveExpiredPasscodes::class),
        'queue:consume' => $containerBuilder->get(ConsumeQueue::class),
        'passcodes:remove_duplicates' => $containerBuilder->get(RemoveDuplicates::class),
        'booking:add' => $containerBuilder->get(AddBooking::class),
        'passcode:remove' => $containerBuilder->get(RemovePasscode::class),
        default => throw new Exception('run parameter not specified'),
    };
    $command->execute(array_slice($argv, 2));
} catch (\Exception $e) {
    Logger::error($e->getMessage());
}

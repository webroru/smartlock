<?php

declare(strict_types=1);

namespace test\App;

use Dotenv\Dotenv;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use tests\App\Unit\UnitTestCase;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load(__DIR__ . '/../config/services.yaml');
$containerBuilder->compile(true);
UnitTestCase::setContainer($containerBuilder);
// Load .env
Dotenv::createImmutable(__DIR__)->load();

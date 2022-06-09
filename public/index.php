<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../bootstrap.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load(__DIR__ . '/../config/services.yaml');
$containerBuilder->compile(true);

$request = Request::createFromGlobals();
$apiController = $containerBuilder->get(App\Controller\ApiController::class);
$response = $apiController->create($request);
$response->prepare($request);
$response->send();

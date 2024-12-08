<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/../bootstrap.php';

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load(__DIR__ . '/../config/services.yaml');
$containerBuilder->compile(true);

$request = Request::createFromGlobals();
$apiController = $containerBuilder->get(App\Controller\ApiController::class);
$path = parse_url($request->getUri(), PHP_URL_PATH);

$response = match ($path) {
    '/api/create' => $apiController->create($request),
    '/api/addPayment' => $apiController->addPayment($request),
    default => new Response(
        'Page not valid',
        Response::HTTP_NOT_FOUND,
        ['content-type' => 'text/html']
    ),
};

$response->prepare($request);
$response->send();

<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\ScienerApi;
use App\Services\LockService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/bootstrap.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder
    ->register('scienerApi', ScienerApi::class);

$containerBuilder
    ->register('lockService', Lockservice::class)
    ->addArgument(new Reference('scienerApi'));

$containerBuilder
    ->register('apiController', ApiController::class)
    ->addArgument(new Reference('lockService'));

$request = Request::createFromGlobals();
$apiController = $containerBuilder->get('apiController');
$response = $apiController->create($request);
$response->prepare($request);
$response->send();

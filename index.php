<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\ScienerApi;
use App\Services\LockService;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/bootstrap.php';

$scienerApi = new ScienerApi();
$lockService = new LockService($scienerApi);

$request = Request::createFromGlobals();
$apiControler = new ApiController($lockService);
$response = $apiControler->create($request);
$response->prepare($request);
$response->send();

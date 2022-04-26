<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\Repository\BookingMysqlRepository;
use App\ScienerApi;
use App\Services\LockService;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/bootstrap.php';

$scienerApi = new ScienerApi();
$bookingRepository = new BookingMysqlRepository();
$lockService = new LockService($scienerApi, $bookingRepository);

$request = Request::createFromGlobals();
$apiControler = new ApiController($lockService);
$response = $apiControler->create($request);
$response->prepare($request);
$response->send();

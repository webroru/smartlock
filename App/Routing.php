<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Routing
{
    public static function dispatch(): void
    {
        $request = Request::createFromGlobals();
        $uri = $request->getRequestUri();
        $controller = explode('/', $uri)[1] ?? null;
        $action = explode('/', $uri)[2] ?? null;

        if (!$controller || !$action) {
            pageNotFound($request);
        }

        $controllerFQN = 'App\Controller\\' . ucfirst($controller) . 'Controller';
        if (!class_exists($controllerFQN) || !method_exists($controllerFQN, $action)) {
            pageNotFound($request);
        }

        $controllerInstance = new $controllerFQN();
        $response = $controllerInstance->$action($request);
        $response->prepare($request);
        $response->send();
    }

    private function pageNotFound($request): void
    {
        $response = new Response(
            'Page not Found',
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/html']
        );
        $response->prepare($request);
        $response->send();
    }
}

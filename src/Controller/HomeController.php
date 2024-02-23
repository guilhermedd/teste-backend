<?php

namespace Contatoseguro\TesteBackend\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\StreamFactory;

class HomeController
{
    public function __construct()
    {
    }

    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = new Response();
        $streamFactory = new StreamFactory();

        $response->getBody()->write('Hello World');

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->withBody($streamFactory->createStream(json_encode(['message' => 'Hello World'])));

    }
}

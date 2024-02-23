<?php

namespace Contatoseguro\TesteBackend\Middleware;

use Exception;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JsonResponseMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $body = $response->getBody()->__toString();

        // Verifica se o corpo parece ser JSON
        if ($this->isJson($body)) {
            return $response->withAddedHeader("Content-Type", "application/json");
        } else {
            return $response->withAddedHeader("Content-Type", "text/html");
        }
    }

    // Função auxiliar para verificar se uma string é JSON válida
    private function isJson($string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

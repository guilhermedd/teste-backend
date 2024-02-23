<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Obter os parâmetros da consulta da URL
        $queryParams = $request->getQueryParams();

        // Obter o ID do admin_user do cabeçalho da solicitação, ou defina um valor padrão se não estiver presente
        $adminUserId = $queryParams['admin_user_id'] ?? 1;

        // Obter o método de ordenação da consulta da URL ou use um padrão
        $ordenationMethod = $queryParams['ordenation_method'] ?? 'id';

        // Definir o método de separar ativos e não ativos
        $isActive = $queryParams['is_active'] ?? null;

        // Definir o método de separar as categorias
        $categoryName = $queryParams['category'] ?? null;

        // Chamar o método getAll do serviço com os parâmetros
        $stm = $this->service->getAll($adminUserId, $ordenationMethod, $isActive, $categoryName);

        // Obter os resultados usando fetchAll
        $products = $stm->fetchAll();

        // Adicionar a lista de produtos ao corpo da resposta
        $response->getBody()->write(json_encode($products));

        // Defina o cabeçalho Content-Type para application/json
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }



    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $productId = $args['id'];
        // Obter o ID do admin_user do cabeçalho da solicitação, ou defina um valor padrão se não estiver presente
        $adminUserId = $request->getHeader('admin_user_id')[0] ?? 1; // Defina um valor padrão (por exemplo, 1)

        // Chamar o método getOne do serviço para obter a lista de produtos
        $stm = $this->service->getOne($productId);

        // Obter os resultados usando fetchAll
        $products = $stm->fetchAll();

        // Adicionar a lista de produtos ao corpo da resposta
        $response->getBody()->write(json_encode($products));

        // Definir o cabeçalho Content-Type para application/json
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = isset($request->getHeader('admin_user_id')[0]) ? $request->getHeader('admin_user_id')[0] : 1;

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(201); // 201 Created
        } else {
            return $response->withStatus(400);
        }
    }


    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }
}

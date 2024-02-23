<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Config\DB;
use Contatoseguro\TesteBackend\Service\CompanyService;
use Contatoseguro\TesteBackend\Service\ProductService;
use DateTime;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReportController
{
    private ProductService $productService;
    private CompanyService $companyService;
    private \PDO $pdo;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
        $this->pdo = DB::connect();
    }

    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $data = [
            ['Id do produto', 'Nome da Empresa', 'Nome do Produto', 'Valor do Produto', 'Categorias do Produto', 'Data de Criação', 'Logs de Alterações']
        ];

        $stm = $this->productService->getAll($adminUserId);
        $products = $stm->fetchAll();

        foreach ($products as $i => $product) {
            $companyName = $this->getCompanyName($product->company_id);
            $productLogs = $this->productService->getLog($product->id)->fetchAll();

            $logsFormatted = [];
            $hasUpdated = false;

            foreach ($productLogs as $log) {
                $adminUserData = $this->getAdminUserData($log->admin_user_id);

                if ($adminUserData) {
                    $logsFormatted[] = "({$adminUserData['name']}, {$log->action}, {$log->timestamp})";
                }

                    if ($log->action === "update" && (!$hasUpdated || $log->timestamp > $whenChangedTemp)) {
                        $hasUpdated = true;
                        $whenChangedTemp = new DateTime($log->timestamp);
                        $whoChangedTemp = $adminUserData['name'];
                    }
            }

            $data[] = [
                $product->id,
                $companyName,
                $product->title,
                $product->price,
                $product->category,
                $product->created_at,
                implode(', ', $logsFormatted),
            ];

            if ($product->title === "iphone 8" && $hasUpdated) {
                $whenChangedOld = $whenChangedTemp;
                $whoChangedOld = $whoChangedTemp;
            }
        }

        print_r("Quem mudou o valor do iphone 8 por último foi: {$whoChangedOld}!\n");

        $report = "<table style='font-size: 10px;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td>{$column}</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";

        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
    }

    private function getCompanyName($companyId)
    {
        $stm = $this->companyService->getNameById($companyId);
        $companyData = $stm->fetch();
        return $companyData ? $companyData->name : '';
    }

    private function getAdminUserData($adminUserId)
    {
        $query = "SELECT * FROM admin_user WHERE id = :adminUserId";
        $stm = $this->pdo->prepare($query);
        $stm->bindParam(':adminUserId', $adminUserId, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetch(PDO::FETCH_ASSOC);
    }
}

<?php

declare(strict_types=1);

namespace Shared\Presentation\Http\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Index Controller (Shared Module)
 *
 * Provides basic health check and API information.
 * This is a shared concern across all bounded contexts.
 */
#[Controller]
class IndexController
{
    #[Inject]
    protected ResponseInterface $response;

    /**
     * Health check and API information endpoint
     *
     * GET /
     */
    #[GetMapping(path: '/')]
    public function index(): PsrResponseInterface
    {
        return $this->response->json([
            'message' => 'API is alive!',
        ]);
    }
}

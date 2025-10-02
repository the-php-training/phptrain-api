<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Tenant\Command\CreateTenantCommand;
use App\Application\Tenant\Command\CreateTenantHandler;
use App\Application\Tenant\DTO\CreateTenantDTO;
use App\Application\Tenant\Query\GetTenantHandler;
use App\Application\Tenant\Query\GetTenantQuery;
use App\Application\Tenant\Query\ListTenantsHandler;
use App\Application\Tenant\Query\ListTenantsQuery;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Tenant HTTP Controller
 *
 * Handles HTTP requests for tenant management
 */
#[Controller(prefix: '/api/tenants')]
class TenantController extends AbstractController
{
    #[Inject]
    protected ValidatorFactoryInterface $validatorFactory;

    #[Inject]
    protected CreateTenantHandler $createTenantHandler;

    #[Inject]
    protected GetTenantHandler $getTenantHandler;

    #[Inject]
    protected ListTenantsHandler $listTenantsHandler;

    /**
     * Create a new tenant
     *
     * POST /api/tenants
     */
    #[PostMapping(path: '')]
    public function create(): ResponseInterface
    {
        try {
            // Validate request
            $validator = $this->validatorFactory->make(
                $this->request->all(),
                [
                    'name' => 'required|string|min:3|max:255',
                    'slug' => 'required|string|min:3|max:50|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    'contact_email' => 'required|email|max:255',
                    'contact_phone' => 'nullable|string|max:20',
                ]
            );

            if ($validator->fails()) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ])->withStatus(422);
            }

            // Create DTO
            $dto = CreateTenantDTO::fromArray($this->request->all());

            // Execute command
            $command = new CreateTenantCommand(
                name: $dto->name,
                slug: $dto->slug,
                contactEmail: $dto->contactEmail,
                contactPhone: $dto->contactPhone,
            );

            $tenantDTO = $this->createTenantHandler->handle($command);

            return $this->response->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => $tenantDTO->toArray(),
            ])->withStatus(201);

        } catch (InvalidArgumentException $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(400);

        } catch (Throwable $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'An error occurred while creating the tenant',
                'error' => $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Get a tenant by ID
     *
     * GET /api/tenants/{id}
     */
    #[GetMapping(path: '/{id}')]
    public function show(string $id): ResponseInterface
    {
        try {
            $query = new GetTenantQuery($id);
            $tenantDTO = $this->getTenantHandler->handle($query);

            return $this->response->json([
                'success' => true,
                'data' => $tenantDTO->toArray(),
            ]);

        } catch (InvalidArgumentException $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(404);

        } catch (Throwable $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the tenant',
                'error' => $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * List all tenants with pagination
     *
     * GET /api/tenants
     */
    #[GetMapping(path: '')]
    public function index(): ResponseInterface
    {
        try {
            $limit = (int) $this->request->input('limit', 20);
            $offset = (int) $this->request->input('offset', 0);

            // Validate pagination
            if ($limit < 1 || $limit > 100) {
                $limit = 20;
            }

            if ($offset < 0) {
                $offset = 0;
            }

            $query = new ListTenantsQuery($limit, $offset);
            $result = $this->listTenantsHandler->handle($query);

            return $this->response->json([
                'success' => true,
                'data' => array_map(fn($dto) => $dto->toArray(), $result['data']),
                'pagination' => [
                    'total' => $result['total'],
                    'limit' => $result['limit'],
                    'offset' => $result['offset'],
                ],
            ]);

        } catch (Throwable $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'An error occurred while listing tenants',
                'error' => $e->getMessage(),
            ])->withStatus(500);
        }
    }
}

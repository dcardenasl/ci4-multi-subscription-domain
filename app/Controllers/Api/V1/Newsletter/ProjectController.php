<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\ProjectCreateRequestDTO;
use App\DTO\Request\Newsletter\ProjectIndexRequestDTO;
use App\DTO\Request\Newsletter\ProjectUpdateRequestDTO;
use App\Interfaces\Newsletter\ProjectServiceInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class ProjectController extends ApiController
{
    protected ProjectServiceInterface $projectService;

    protected function resolveDefaultService(): ProjectServiceInterface
    {
        $this->projectService = Services::projectService();

        return $this->projectService;
    }

    protected array $statusCodes = [
        'store' => 201,
    ];

    public function index(): ResponseInterface
    {
        return $this->handleRequest(
            function (ProjectIndexRequestDTO $dto, SecurityContext $context): mixed {
                if (! $context->hasPermission('newsletter.projects.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->projectService->index($dto, $context);
            },
            ProjectIndexRequestDTO::class
        );
    }

    public function create(): ResponseInterface
    {
        return $this->handleRequest(
            function (ProjectCreateRequestDTO $dto, SecurityContext $context): mixed {
                if (! $context->hasPermission('newsletter.projects.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->projectService->store($dto, $context);
            },
            ProjectCreateRequestDTO::class
        );
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (ProjectUpdateRequestDTO $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.projects.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->projectService->update($id, $dto, $context);
            },
            ProjectUpdateRequestDTO::class
        );
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.projects.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->projectService->show($id, $context);
            }
        );
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.projects.delete')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->projectService->destroy($id, $context);
            }
        );
    }

    public function config(string $projectKey): ResponseInterface
    {
        return $this->handleRequest(fn ($dto, $context) => $this->projectService->landingConfig($projectKey, $context));
    }
}

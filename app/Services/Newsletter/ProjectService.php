<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\DTO\Response\Newsletter\LandingConfigResponseDTO;
use App\Entities\ProjectEntity;
use App\Interfaces\Newsletter\ProjectServiceInterface;
use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\NotFoundException;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<ProjectEntity>
 */
class ProjectService extends BaseCrudService implements ProjectServiceInterface
{
    /**
     * @param RepositoryInterface<ProjectEntity> $projectRepository
     */
    public function __construct(
        RepositoryInterface $projectRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($projectRepository, $responseMapper);
    }

    public function landingConfig(string $projectKey, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        $entity = $this->repository->getModel()->where('project_key', $projectKey)->first();

        if ($entity === null) {
            throw new NotFoundException(lang('Api.resourceNotFound'));
        }

        $data = is_array($entity) ? $entity : (method_exists($entity, 'toRawArray') ? $entity->toRawArray() : (array) $entity);

        return LandingConfigResponseDTO::fromArray($data);
    }
}

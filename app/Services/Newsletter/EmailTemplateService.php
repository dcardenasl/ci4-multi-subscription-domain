<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\EmailTemplateEntity;
use App\Interfaces\Newsletter\EmailTemplateServiceInterface;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<EmailTemplateEntity>
 */
class EmailTemplateService extends BaseCrudService implements EmailTemplateServiceInterface
{
    /**
     * @param RepositoryInterface<EmailTemplateEntity> $emailTemplateRepository
     */
    public function __construct(
        RepositoryInterface $emailTemplateRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($emailTemplateRepository, $responseMapper);
    }

    /**
     * Domain Hooks
     *
     * Implement beforeStore, afterStore, beforeUpdate, etc.,
     * to add specific business logic while keeping the service layer clean.
     */

    // Custom methods declared in EmailTemplateServiceInterface must be implemented here.
    // Until fully implemented, throw to avoid silent incorrect behavior:
    //   throw new \BadMethodCallException(__METHOD__ . ' not implemented');
}

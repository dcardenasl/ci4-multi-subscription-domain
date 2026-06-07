<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\CampaignEntity;
use App\Interfaces\Newsletter\CampaignServiceInterface;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<CampaignEntity>
 */
class CampaignService extends BaseCrudService implements CampaignServiceInterface
{
    /**
     * @param RepositoryInterface<CampaignEntity> $campaignRepository
     */
    public function __construct(
        RepositoryInterface $campaignRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($campaignRepository, $responseMapper);
    }

    /**
     * Domain Hooks
     *
     * Implement beforeStore, afterStore, beforeUpdate, etc.,
     * to add specific business logic while keeping the service layer clean.
     */

    // Custom methods declared in CampaignServiceInterface must be implemented here.
    // Until fully implemented, throw to avoid silent incorrect behavior:
    //   throw new \BadMethodCallException(__METHOD__ . ' not implemented');
}

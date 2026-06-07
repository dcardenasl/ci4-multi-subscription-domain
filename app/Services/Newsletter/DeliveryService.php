<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\DeliveryEntity;
use App\Interfaces\Newsletter\DeliveryServiceInterface;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<DeliveryEntity>
 */
class DeliveryService extends BaseCrudService implements DeliveryServiceInterface
{
    /**
     * @param RepositoryInterface<DeliveryEntity> $deliveryRepository
     */
    public function __construct(
        RepositoryInterface $deliveryRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($deliveryRepository, $responseMapper);
    }

    /**
     * Domain Hooks
     *
     * Implement beforeStore, afterStore, beforeUpdate, etc.,
     * to add specific business logic while keeping the service layer clean.
     */

    // Custom methods declared in DeliveryServiceInterface must be implemented here.
    // Until fully implemented, throw to avoid silent incorrect behavior:
    //   throw new \BadMethodCallException(__METHOD__ . ' not implemented');
}

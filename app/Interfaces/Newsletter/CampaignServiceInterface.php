<?php

declare(strict_types=1);

namespace App\Interfaces\Newsletter;

use dcardenasl\Ci4ApiCore\Services\CrudServiceContract;

interface CampaignServiceInterface extends CrudServiceContract
{
    /**
     * Dispatch the campaign (draft|scheduled -> sending -> sent)
     */
    public function dispatch(int $id, ?\dcardenasl\Ci4ApiCore\Dto\SecurityContext $context = null): \dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;

    /**
     * Cancel the campaign (draft|scheduled -> cancelled)
     */
    public function cancel(int $id, ?\dcardenasl\Ci4ApiCore\Dto\SecurityContext $context = null): \dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
}

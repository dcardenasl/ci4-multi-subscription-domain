<?php

declare(strict_types=1);

namespace App\Interfaces\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Services\CrudServiceContract;

interface ProjectServiceInterface extends CrudServiceContract
{
    public function landingConfig(string $projectKey, ?SecurityContext $context = null): DataTransferObjectInterface;
}

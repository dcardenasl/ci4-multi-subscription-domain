<?php

declare(strict_types=1);

namespace App\Interfaces\Newsletter;

use dcardenasl\Ci4ApiCore\Services\CrudServiceContract;

interface EmailTemplateServiceInterface extends CrudServiceContract
{
    // Declare resource-specific service methods here.
    // Implement them in EmailTemplateService; until ready, throw:
    //   throw new \BadMethodCallException(__METHOD__ . ' not implemented');
}

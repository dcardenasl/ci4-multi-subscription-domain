<?php

declare(strict_types=1);

namespace App\Interfaces\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Services\CrudServiceContract;

interface SubscriberServiceInterface extends CrudServiceContract
{
    public function subscribe(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface;

    public function confirm(string $token, ?SecurityContext $context = null): DataTransferObjectInterface;

    public function unsubscribe(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface;

    public function handleBounce(string $email): void;

    public function handleComplaint(string $email): void;
}

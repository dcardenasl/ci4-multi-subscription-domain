<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\DeliveryCreateRequestDTO;
use App\DTO\Request\Newsletter\DeliveryIndexRequestDTO;
use App\DTO\Request\Newsletter\DeliveryUpdateRequestDTO;
use App\Interfaces\Newsletter\DeliveryServiceInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class DeliveryController extends ApiController
{
    protected DeliveryServiceInterface $deliveryService;

    protected function resolveDefaultService(): DeliveryServiceInterface
    {
        $this->deliveryService = Services::deliveryService();

        return $this->deliveryService;
    }

    protected array $statusCodes = [
        'store' => 201,
    ];

    public function index(): ResponseInterface
    {
        return $this->handleRequest(
            function (DeliveryIndexRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.deliveries.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->deliveryService->index($dto, $context);
            },
            DeliveryIndexRequestDTO::class
        );
    }

    public function create(): ResponseInterface
    {
        return $this->handleRequest(
            function (DeliveryCreateRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.deliveries.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->deliveryService->store($dto, $context);
            },
            DeliveryCreateRequestDTO::class
        );
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (DeliveryUpdateRequestDTO $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.deliveries.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->deliveryService->update($id, $dto, $context);
            },
            DeliveryUpdateRequestDTO::class
        );
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.deliveries.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->deliveryService->show($id, $context);
            }
        );
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.deliveries.delete')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->deliveryService->destroy($id, $context);
            }
        );
    }
}

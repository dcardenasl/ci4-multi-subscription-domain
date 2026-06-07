<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\PublicSubscribeRequestDTO;
use App\DTO\Request\Newsletter\SubscriberIndexRequestDTO;
use App\DTO\Request\Newsletter\SubscriberUpdateRequestDTO;
use App\DTO\Request\Newsletter\UnsubscribeRequestDTO;
use App\Interfaces\Newsletter\SubscriberServiceInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class SubscriberController extends ApiController
{
    protected SubscriberServiceInterface $subscriberService;

    protected function resolveDefaultService(): SubscriberServiceInterface
    {
        $this->subscriberService = Services::subscriberService();

        return $this->subscriberService;
    }

    protected array $statusCodes = [
        'store' => 201,
    ];

    public function index(): ResponseInterface
    {
        return $this->handleRequest(
            function (SubscriberIndexRequestDTO $dto, SecurityContext $context): mixed {
                if (! $context->hasPermission('newsletter.subscribers.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->subscriberService->index($dto, $context);
            },
            SubscriberIndexRequestDTO::class
        );
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (SubscriberUpdateRequestDTO $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.subscribers.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->subscriberService->update($id, $dto, $context);
            },
            SubscriberUpdateRequestDTO::class
        );
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.subscribers.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->subscriberService->show($id, $context);
            }
        );
    }

    public function subscribe(): ResponseInterface
    {
        return $this->handleRequest(
            fn ($dto, $context) => $this->subscriberService->subscribe($dto, $context),
            PublicSubscribeRequestDTO::class
        );
    }

    public function confirm(string $token): ResponseInterface
    {
        return $this->handleRequest(fn ($dto, $context) => $this->subscriberService->confirm($token, $context));
    }

    public function unsubscribe(): ResponseInterface
    {
        return $this->handleRequest(
            fn ($dto, $context) => $this->subscriberService->unsubscribe($dto, $context),
            UnsubscribeRequestDTO::class
        );
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.subscribers.delete')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->subscriberService->destroy($id, $context);
            }
        );
    }
}

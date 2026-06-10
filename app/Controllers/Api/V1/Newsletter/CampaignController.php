<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\CampaignCreateRequestDTO;
use App\DTO\Request\Newsletter\CampaignIndexRequestDTO;
use App\DTO\Request\Newsletter\CampaignUpdateRequestDTO;
use App\Interfaces\Newsletter\CampaignServiceInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class CampaignController extends ApiController
{
    protected CampaignServiceInterface $campaignService;

    protected function resolveDefaultService(): CampaignServiceInterface
    {
        $this->campaignService = Services::campaignService();

        return $this->campaignService;
    }

    protected array $statusCodes = [
        'store' => 201,
    ];

    public function index(): ResponseInterface
    {
        return $this->handleRequest(
            function (CampaignIndexRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.campaigns.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->index($dto, $context);
            },
            CampaignIndexRequestDTO::class
        );
    }

    public function create(): ResponseInterface
    {
        return $this->handleRequest(
            function (CampaignCreateRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.campaigns.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->store($dto, $context);
            },
            CampaignCreateRequestDTO::class
        );
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (CampaignUpdateRequestDTO $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.campaigns.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->update($id, $dto, $context);
            },
            CampaignUpdateRequestDTO::class
        );
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.campaigns.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->show($id, $context);
            }
        );
    }

    public function stats(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (! $context->hasPermission('newsletter.campaigns.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }

                return $this->campaignService->stats($id, $context);
            }
        );
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.campaigns.delete')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->destroy($id, $context);
            }
        );
    }

    public function dispatch(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.campaigns.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->dispatch($id, $context);
            }
        );
    }

    public function cancel(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.campaigns.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->campaignService->cancel($id, $context);
            }
        );
    }
}

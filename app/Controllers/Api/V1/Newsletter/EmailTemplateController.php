<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\EmailTemplateCreateRequestDTO;
use App\DTO\Request\Newsletter\EmailTemplateIndexRequestDTO;
use App\DTO\Request\Newsletter\EmailTemplateUpdateRequestDTO;
use App\Interfaces\Newsletter\EmailTemplateServiceInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class EmailTemplateController extends ApiController
{
    protected EmailTemplateServiceInterface $emailTemplateService;

    protected function resolveDefaultService(): EmailTemplateServiceInterface
    {
        $this->emailTemplateService = Services::emailTemplateService();

        return $this->emailTemplateService;
    }

    protected array $statusCodes = [
        'store' => 201,
    ];

    public function index(): ResponseInterface
    {
        return $this->handleRequest(
            function (EmailTemplateIndexRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.emailtemplates.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->emailTemplateService->index($dto, $context);
            },
            EmailTemplateIndexRequestDTO::class
        );
    }

    public function create(): ResponseInterface
    {
        return $this->handleRequest(
            function (EmailTemplateCreateRequestDTO $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.emailtemplates.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->emailTemplateService->store($dto, $context);
            },
            EmailTemplateCreateRequestDTO::class
        );
    }

    public function update(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (EmailTemplateUpdateRequestDTO $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.emailtemplates.write')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->emailTemplateService->update($id, $dto, $context);
            },
            EmailTemplateUpdateRequestDTO::class
        );
    }

    public function show(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.emailtemplates.read')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->emailTemplateService->show($id, $context);
            }
        );
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context) use ($id): mixed {
                if (!$context->hasPermission('newsletter.emailtemplates.delete')) {
                    throw new \dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException(lang('Api.forbidden'));
                }
                return $this->emailTemplateService->destroy($id, $context);
            }
        );
    }
}

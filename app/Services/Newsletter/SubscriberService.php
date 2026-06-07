<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\SubscriberEntity;
use App\Interfaces\Newsletter\SubscriberServiceInterface;
use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\NotFoundException;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<SubscriberEntity>
 */
class SubscriberService extends BaseCrudService implements SubscriberServiceInterface
{
    /**
     * @param RepositoryInterface<SubscriberEntity> $subscriberRepository
     */
    public function __construct(
        RepositoryInterface $subscriberRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($subscriberRepository, $responseMapper);
    }

    public function subscribe(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($dto, $context) {
            $data = $dto->toArray();

            $projectResult = model(\App\Models\ProjectModel::class)->where('project_key', $data['project_key'])->first();

            if (! is_object($projectResult)) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $existingResult = model(\App\Models\SubscriberModel::class)
                ->where('project_id', $projectResult->id)
                ->where('email', $data['email'])
                ->whereNotIn('status', ['unsubscribed'])
                ->first();

            if (is_object($existingResult)) {
                throw new ValidationException(lang('Api.validationFailed'), ['email' => lang('Validation.already_subscribed')]);
            }

            $doubleOptIn = (bool) ($projectResult->double_opt_in_enabled ?? false);

            $payload = [
                'project_id'        => $projectResult->id,
                'email'             => $data['email'],
                'status'            => $doubleOptIn ? 'pending' : 'confirmed',
                'confirm_token'     => $doubleOptIn ? bin2hex(random_bytes(32)) : null,
                'unsubscribe_token' => bin2hex(random_bytes(32)),
                'invitation_code'   => $data['invitation_code'] ?? null,
                'confirmed_at'      => $doubleOptIn ? null : date('Y-m-d H:i:s'),
            ];

            $id = $this->repository->insert($payload);

            if ($id === false || $id === 0 || $id === '') {
                throw new ValidationException(lang('Api.validationFailed'), $this->repository->errors());
            }

            if ($id === true) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $entity = $this->repository->find($id);

            if ($entity === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $this->afterStore($entity, $context);

            return $this->responseMapper->map($entity);
        });
    }

    public function confirm(string $token, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($token) {
            $result = $this->repository->getModel()->where('confirm_token', $token)->first();

            if (! ($result instanceof \App\Entities\SubscriberEntity)) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            if ($result->status === 'confirmed') {
                return $this->responseMapper->map($result);
            }

            $this->repository->setEntityContext($result->id, $result);
            $this->repository->update($result->id, [
                'status'        => 'confirmed',
                'confirmed_at'  => date('Y-m-d H:i:s'),
                'confirm_token' => null,
            ]);

            $updated = $this->repository->find($result->id);

            if ($updated === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            return $this->responseMapper->map($updated);
        });
    }

    public function unsubscribe(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($dto) {
            $token = $dto->toArray()['token'];

            $result = $this->repository->getModel()->where('unsubscribe_token', $token)->first();

            if (! ($result instanceof \App\Entities\SubscriberEntity)) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            if ($result->status === 'unsubscribed') {
                return $this->responseMapper->map($result);
            }

            $this->repository->setEntityContext($result->id, $result);
            $this->repository->update($result->id, [
                'status'          => 'unsubscribed',
                'unsubscribed_at' => date('Y-m-d H:i:s'),
            ]);

            $updated = $this->repository->find($result->id);

            if ($updated === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            return $this->responseMapper->map($updated);
        });
    }
}

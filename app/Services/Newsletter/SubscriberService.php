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
    private const IMPORT_MAX_ROWS = 1000;

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
                'first_name'        => $data['first_name'] ?? null,
                'locale'            => $data['locale'] ?? $projectResult->locale_default ?? 'en',
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

            if ($doubleOptIn && !empty($entity->confirm_token)) {
                service('queueManager')->push(\App\Queue\Jobs\SendDoubleOptInEmailJob::class, [
                    'subscriber_id' => $entity->id,
                ]);
            }

            return $this->responseMapper->map($entity);
        });
    }

    /**
     * Bulk import of subscribers into a project. Unlike subscribe(), rows are
     * inserted silently: no double opt-in email is queued and the status comes
     * from the row (default `confirmed`). Invalid or duplicate rows are skipped
     * and reported per-row instead of failing the whole batch.
     */
    public function import(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($dto) {
            $data = $dto->toArray();
            $projectId = (int) $data['project_id'];
            /** @var list<mixed> $rows */
            $rows = $data['rows'];

            if ($rows === []) {
                throw new ValidationException(lang('Api.validationFailed'), ['rows' => lang('Validation.import_rows_required')]);
            }

            if (count($rows) > self::IMPORT_MAX_ROWS) {
                throw new ValidationException(lang('Api.validationFailed'), ['rows' => lang('Validation.import_rows_limit', [self::IMPORT_MAX_ROWS])]);
            }

            $projectResult = model(\App\Models\ProjectModel::class)->find($projectId);

            if (! is_object($projectResult)) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $now = date('Y-m-d H:i:s');
            $imported = 0;
            $errors = [];
            $seen = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 1;

                if (! is_array($row)) {
                    $errors[] = ['row' => $rowNumber, 'email' => '', 'reason' => 'invalid_row'];
                    continue;
                }

                $email = strtolower(trim((string) ($row['email'] ?? '')));

                if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[] = ['row' => $rowNumber, 'email' => $email, 'reason' => 'invalid_email'];
                    continue;
                }

                if (isset($seen[$email])) {
                    $errors[] = ['row' => $rowNumber, 'email' => $email, 'reason' => 'duplicate_in_file'];
                    continue;
                }
                $seen[$email] = true;

                $status = trim((string) ($row['status'] ?? ''));
                if ($status === '') {
                    $status = 'confirmed';
                }

                if (! in_array($status, ['pending', 'confirmed', 'unsubscribed', 'bounced'], true)) {
                    $errors[] = ['row' => $rowNumber, 'email' => $email, 'reason' => 'invalid_status'];
                    continue;
                }

                $exists = model(\App\Models\SubscriberModel::class)
                    ->where('subscribers.project_id', $projectId)
                    ->where('subscribers.email', $email)
                    ->countAllResults();

                if ($exists > 0) {
                    $errors[] = ['row' => $rowNumber, 'email' => $email, 'reason' => 'already_subscribed'];
                    continue;
                }

                $firstName = trim((string) ($row['first_name'] ?? ''));
                $locale = trim((string) ($row['locale'] ?? ''));
                $invitationCode = trim((string) ($row['invitation_code'] ?? ''));

                $id = $this->repository->insert([
                    'project_id'        => $projectId,
                    'email'             => $email,
                    'first_name'        => $firstName !== '' ? $firstName : null,
                    'locale'            => $locale !== '' ? $locale : ($projectResult->locale_default ?? 'en'),
                    'status'            => $status,
                    'confirm_token'     => null,
                    'unsubscribe_token' => bin2hex(random_bytes(32)),
                    'invitation_code'   => $invitationCode !== '' ? $invitationCode : null,
                    'confirmed_at'      => $status === 'confirmed' ? $now : null,
                    'unsubscribed_at'   => in_array($status, ['unsubscribed', 'bounced'], true) ? $now : null,
                ]);

                if ($id === false || $id === 0 || $id === '' || $id === true) {
                    $errors[] = ['row' => $rowNumber, 'email' => $email, 'reason' => 'persist_failed'];
                    continue;
                }

                $imported++;
            }

            return new \App\DTO\Response\Newsletter\SubscriberImportResultResponseDTO(
                total: count($rows),
                imported: $imported,
                skipped: count($errors),
                errors: $errors,
            );
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

    public function handleBounce(string $email): void
    {
        $this->wrapInTransaction(function () use ($email): void {
            $subscribers = $this->repository->getModel()->where('email', $email)->findAll();
            foreach ($subscribers as $subscriber) {
                if (!($subscriber instanceof \App\Entities\SubscriberEntity)) {
                    continue;
                }
                if ($subscriber->status !== 'bounced') {
                    $this->repository->setEntityContext($subscriber->id, $subscriber);
                    $res = $this->repository->update($subscriber->id, [
                        'status'          => 'bounced',
                        'unsubscribed_at' => date('Y-m-d H:i:s'),
                    ]);
                    if ($res === false) {
                        log_message('error', 'BOUNCE UPDATE FAILED: ' . json_encode($this->repository->errors()));
                    }
                }
            }
        });
    }

    public function handleComplaint(string $email): void
    {
        $this->wrapInTransaction(function () use ($email): void {
            $subscribers = $this->repository->getModel()->where('email', $email)->findAll();
            foreach ($subscribers as $subscriber) {
                if (!($subscriber instanceof \App\Entities\SubscriberEntity)) {
                    continue;
                }
                if ($subscriber->status !== 'unsubscribed') {
                    $this->repository->setEntityContext($subscriber->id, $subscriber);
                    $this->repository->update($subscriber->id, [
                        'status'          => 'unsubscribed',
                        'unsubscribed_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        });
    }
}

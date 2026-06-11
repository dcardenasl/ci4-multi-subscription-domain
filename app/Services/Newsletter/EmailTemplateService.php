<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\EmailTemplateEntity;
use App\Interfaces\Newsletter\EmailTemplateServiceInterface;
use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\NotFoundException;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<EmailTemplateEntity>
 */
class EmailTemplateService extends BaseCrudService implements EmailTemplateServiceInterface
{
    /**
     * @param RepositoryInterface<EmailTemplateEntity> $emailTemplateRepository
     */
    public function __construct(
        RepositoryInterface $emailTemplateRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($emailTemplateRepository, $responseMapper);
    }

    public function store(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($dto, $context) {
            $data = $dto->toArray();
            $translations = $data['translations'] ?? [];
            unset($data['translations']);

            $id = $this->repository->insert($data);
            if ($id === false || $id === 0 || $id === '' || $id === true) {
                throw new ValidationException(lang('Api.validationFailed'), $this->repository->errors());
            }

            if (!empty($translations) && is_array($translations)) {
                $translationModel = model(\App\Models\EmailTemplateTranslationModel::class);
                foreach ($translations as $trans) {
                    $translationModel->insert([
                        'email_template_id' => $id,
                        'locale'            => $trans['locale'],
                        'subject'           => $trans['subject'],
                        'html_body'         => $trans['html_body'],
                        'text_body'         => $trans['text_body'] ?? null,
                    ]);
                }
            }

            $entity = $this->repository->find($id);
            if ($entity === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $this->afterStore($entity, $context);
            return $this->responseMapper->map($entity);
        });
    }

    public function update(int $id, DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($id, $dto, $context) {
            $data = $dto->toArray();
            $translations = $data['translations'] ?? null;
            unset($data['translations']);

            $entity = $this->repository->find($id);
            if ($entity === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $this->repository->setEntityContext($id, $entity);
            if (!$this->repository->update($id, $data)) {
                throw new ValidationException(lang('Api.validationFailed'), $this->repository->errors());
            }

            if ($translations !== null && is_array($translations)) {
                $translationModel = model(\App\Models\EmailTemplateTranslationModel::class);
                $translationModel->where('email_template_id', $id)->delete();

                foreach ($translations as $trans) {
                    $translationModel->insert([
                        'email_template_id' => $id,
                        'locale'            => $trans['locale'],
                        'subject'           => $trans['subject'],
                        'html_body'         => $trans['html_body'],
                        'text_body'         => $trans['text_body'] ?? null,
                    ]);
                }
            }

            $updated = $this->repository->find($id);
            if ($updated === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            $this->afterUpdate($updated, $context);
            return $this->responseMapper->map($updated);
        });
    }

    /**
     * Domain Hooks
     *
     * Implement beforeStore, afterStore, beforeUpdate, etc.,
     * to add specific business logic while keeping the service layer clean.
     */

    // Custom methods declared in EmailTemplateServiceInterface must be implemented here.
    // Until fully implemented, throw to avoid silent incorrect behavior:
    //   throw new \BadMethodCallException(__METHOD__ . ' not implemented');
}

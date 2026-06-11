<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\DTO\Response\Newsletter\CampaignStatsResponseDTO;
use App\Entities\CampaignEntity;
use App\Interfaces\Newsletter\CampaignServiceInterface;
use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\NotFoundException;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\BaseCrudService;

/**
 * @extends BaseCrudService<CampaignEntity>
 */
class CampaignService extends BaseCrudService implements CampaignServiceInterface
{
    /**
     * @param RepositoryInterface<CampaignEntity> $campaignRepository
     */
    public function __construct(
        RepositoryInterface $campaignRepository,
        ResponseMapperInterface $responseMapper
    ) {
        parent::__construct($campaignRepository, $responseMapper);
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
                $translationModel = model(\App\Models\CampaignTranslationModel::class);
                foreach ($translations as $trans) {
                    $translationModel->insert([
                        'campaign_id' => $id,
                        'locale'      => $trans['locale'],
                        'subject'     => $trans['subject'],
                        'html_body'   => $trans['html_body'],
                        'text_body'   => $trans['text_body'] ?? null,
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
                $translationModel = model(\App\Models\CampaignTranslationModel::class);
                $translationModel->where('campaign_id', $id)->delete();

                foreach ($translations as $trans) {
                    $translationModel->insert([
                        'campaign_id' => $id,
                        'locale'      => $trans['locale'],
                        'subject'     => $trans['subject'],
                        'html_body'   => $trans['html_body'],
                        'text_body'   => $trans['text_body'] ?? null,
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

    public function dispatch(int $id, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($id) {
            $campaign = $this->repository->find($id);
            if ($campaign === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            if (! in_array($campaign->status, ['draft', 'scheduled'], true)) {
                throw new ValidationException(
                    lang('Api.validationFailed'),
                    ['status' => lang('Campaigns.invalid_status_for_dispatch')]
                );
            }

            $now = date('Y-m-d H:i:s');

            // 1. Mark campaign as sending
            $this->repository->setEntityContext($id, $campaign);
            $this->repository->update($id, [
                'status' => 'sending',
                'send_started_at' => $now,
            ]);

            // 2. Fetch all confirmed subscribers for the project
            $subscriberModel = model(\App\Models\SubscriberModel::class);
            $deliveryModel = model(\App\Models\DeliveryModel::class);
            $queueManager = service('queueManager');

            $subscribers = $subscriberModel->where('project_id', $campaign->project_id)
                ->where('status', 'confirmed')
                ->findAll();

            if (empty($subscribers)) {
                $this->repository->update($id, [
                    'status' => 'sent',
                    'sent_at' => $now,
                ]);

                $updated = $this->repository->find($id);
                if ($updated === null) {
                    throw new NotFoundException(lang('Api.resourceNotFound'));
                }

                return $this->responseMapper->map($updated);
            }

            foreach ($subscribers as $subscriber) {
                // 3. Create delivery row
                $deliveryData = [
                    'project_id'          => $campaign->project_id,
                    'campaign_id'         => $campaign->id,
                    'subscriber_id'       => $subscriber->id,
                    'email'               => $subscriber->email,
                    'status'              => 'pending',
                    'attempts'            => 0,
                    'last_error'          => '',
                    'provider_message_id' => '',
                    'delivery_token'      => bin2hex(random_bytes(16)),
                    'sent_at'             => '1000-01-01 00:00:00',
                ];

                $deliveryId = $deliveryModel->insert($deliveryData);

                if ($deliveryId) {
                    // 4. Push job to queue
                    $queueManager->push(\App\Queue\Jobs\SendCampaignJob::class, [
                        'delivery_id' => $deliveryId,
                    ]);
                }
            }

            // 5. Update campaign status to sent (all emails queued)
            $this->repository->update($id, [
                'status' => 'sent',
                'sent_at' => $now,
            ]);

            $updated = $this->repository->find($id);
            if ($updated === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            return $this->responseMapper->map($updated);
        });
    }

    public function stats(int $id, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        $campaign = $this->repository->find($id);
        if ($campaign === null) {
            throw new NotFoundException(lang('Api.resourceNotFound'));
        }

        $deliveryModel = model(\App\Models\DeliveryModel::class);

        $totalDeliveries = (int) $deliveryModel->where('campaign_id', $id)->countAllResults();
        $sentCount = (int) $deliveryModel->where('campaign_id', $id)->where('status', 'sent')->countAllResults();
        $failedCount = (int) $deliveryModel->where('campaign_id', $id)->where('status', 'failed')->countAllResults();
        $openedCount = (int) $deliveryModel->where('campaign_id', $id)->where('opened_at IS NOT NULL', null, false)->countAllResults();
        $clickedCount = (int) $deliveryModel->where('campaign_id', $id)->where('clicked_at IS NOT NULL', null, false)->countAllResults();

        $bouncedCount = (int) $deliveryModel->builder()
            ->join('subscribers', 'subscribers.id = deliveries.subscriber_id', 'left')
            ->where('deliveries.campaign_id', $id)
            ->where('subscribers.status', 'bounced')
            ->countAllResults();

        $openRate = $sentCount > 0 ? round(($openedCount / $sentCount) * 100, 1) : 0.0;
        $clickRate = $sentCount > 0 ? round(($clickedCount / $sentCount) * 100, 1) : 0.0;

        return new CampaignStatsResponseDTO(
            campaign_id: (int) $campaign->id,
            total_deliveries: $totalDeliveries,
            sent_count: $sentCount,
            failed_count: $failedCount,
            bounced_count: $bouncedCount,
            opened_count: $openedCount,
            clicked_count: $clickedCount,
            open_rate: $openRate,
            click_rate: $clickRate,
        );
    }

    public function cancel(int $id, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($id) {
            $campaign = $this->repository->find($id);
            if ($campaign === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            if (! in_array($campaign->status, ['draft', 'scheduled'], true)) {
                throw new ValidationException(
                    lang('Api.validationFailed'),
                    ['status' => lang('Campaigns.invalid_status_for_cancel')]
                );
            }

            $this->repository->setEntityContext($id, $campaign);
            $this->repository->update($id, [
                'status' => 'cancelled',
            ]);

            $updated = $this->repository->find($id);
            if ($updated === null) {
                throw new NotFoundException(lang('Api.resourceNotFound'));
            }

            return $this->responseMapper->map($updated);
        });
    }
}

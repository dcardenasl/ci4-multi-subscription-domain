<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

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

<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use CodeIgniter\HTTP\ResponseInterface;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class TrackingController extends ApiController
{
    protected function resolveDefaultService(): object
    {
        return model(DeliveryModel::class);
    }

    public function open(string $token): ResponseInterface
    {
        return $this->handleRequest(function () use ($token): ResponseInterface {
            $deliveryModel = model(DeliveryModel::class);
            $delivery = $deliveryModel->where('delivery_token', $token)->first();

            if ($delivery !== null) {
                $updateData = [];

                if (empty($delivery->opened_at) || $delivery->opened_at === '1000-01-01 00:00:00') {
                    $now = date('Y-m-d H:i:s');
                    $updateData['opened_at'] = $now;

                    // Increment opened_count on the campaign
                    $campaignModel = model(CampaignModel::class);
                    $campaign = $campaignModel->find($delivery->campaign_id);
                    if ($campaign !== null) {
                        $campaignModel->update($campaign->id, [
                            'opened_count' => ($campaign->opened_count ?? 0) + 1,
                        ]);
                    }
                }

                if (!empty($updateData)) {
                    $deliveryModel->update($delivery->id, $updateData);
                }
            }

            // Return 1x1 transparent GIF
            $gif = (string) base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', true);
            return $this->response
                ->setHeader('Content-Type', 'image/gif')
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setHeader('Pragma', 'no-cache')
                ->setBody($gif);
        });
    }

    public function click(string $token): ResponseInterface
    {
        return $this->handleRequest(function () use ($token): ResponseInterface {
            $url = $this->request->getGet('url');
            if (empty($url) || !is_string($url)) {
                return $this->response->setStatusCode(400)->setBody('Missing target URL');
            }

            $deliveryModel = model(DeliveryModel::class);
            $delivery = $deliveryModel->where('delivery_token', $token)->first();

            if ($delivery !== null) {
                $now = date('Y-m-d H:i:s');
                $updateData = [
                    'clicks_count' => ($delivery->clicks_count ?? 0) + 1,
                ];

                if (empty($delivery->clicked_at) || $delivery->clicked_at === '1000-01-01 00:00:00') {
                    $updateData['clicked_at'] = $now;

                    // Increment clicked_count on the campaign
                    $campaignModel = model(CampaignModel::class);
                    $campaign = $campaignModel->find($delivery->campaign_id);
                    if ($campaign !== null) {
                        $campaignModel->update($campaign->id, [
                            'clicked_count' => ($campaign->clicked_count ?? 0) + 1,
                        ]);
                    }
                }

                $deliveryModel->update($delivery->id, $updateData);
            }

            return $this->response->redirect($url);
        });
    }
}

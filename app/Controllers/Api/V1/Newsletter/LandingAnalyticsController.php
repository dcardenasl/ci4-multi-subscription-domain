<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\DTO\Request\Newsletter\LandingAnalyticsEventsRequestDTO;
use App\Interfaces\Newsletter\LandingAnalyticsServiceInterface;
use dcardenasl\Ci4ApiCore\Http\ApiController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Newsletter Analytics', description: 'Landing analytics endpoints')]
class LandingAnalyticsController extends ApiController
{
    public function __construct(
        private readonly LandingAnalyticsServiceInterface $analyticsService,
    ) {
    }

    protected function resolveDefaultService(): object
    {
        return $this->analyticsService;
    }

    #[OA\Post(
        path: '/api/v1/newsletter/analytics/events',
        summary: 'Ingest analytics events batch',
        tags: ['Newsletter Analytics'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LandingAnalyticsEventsRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Events ingested'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function ingest(): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->ingest(
                new LandingAnalyticsEventsRequestDTO($request->getJSON())
            )
        );
    }

    #[OA\Get(
        path: '/api/v1/newsletter/analytics/overview',
        summary: 'Get analytics overview',
        tags: ['Newsletter Analytics'],
        parameters: [
            new OA\Parameter(name: 'project_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Overview data'),
        ]
    )]
    public function overview(): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->overview($request->getArrayVar('filter') ?? [])
        );
    }

    #[OA\Get(
        path: '/api/v1/newsletter/analytics/funnel',
        summary: 'Get funnel analysis',
        tags: ['Newsletter Analytics'],
        responses: [
            new OA\Response(response: 200, description: 'Funnel data'),
        ]
    )]
    public function funnel(): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->funnel($request->getArrayVar('filter') ?? [])
        );
    }

    #[OA\Get(
        path: '/api/v1/newsletter/analytics/sessions',
        summary: 'List analytics sessions',
        tags: ['Newsletter Analytics'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Sessions list'),
        ]
    )]
    public function sessions(): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->sessions($request->getArrayVar('filter') ?? [])
        );
    }

    #[OA\Get(
        path: '/api/v1/newsletter/analytics/sessions/{id}',
        summary: 'Get session details',
        tags: ['Newsletter Analytics'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Session details'),
            new OA\Response(response: 404, description: 'Session not found'),
        ]
    )]
    public function session(int $id): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->session($id, $request->getArrayVar('filter') ?? [])
        );
    }

    #[OA\Get(
        path: '/api/v1/newsletter/subscribers/{subscriber_id}/journey',
        summary: 'Get subscriber landing journey',
        tags: ['Newsletter Analytics'],
        parameters: [
            new OA\Parameter(name: 'subscriber_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Journey data'),
        ]
    )]
    public function journey(int $subscriber_id): void
    {
        $this->handleRequest(
            fn ($request) => $this->analyticsService->journey($subscriber_id, $request->getArrayVar('filter') ?? [])
        );
    }
}

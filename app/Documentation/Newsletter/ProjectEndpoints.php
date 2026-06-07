<?php

declare(strict_types=1);

namespace App\Documentation\Newsletter;

use OpenApi\Attributes as OA;

/**
 * OpenAPI definitions for Project endpoints.
 *
 * @OA\Tag(name="Newsletter", description="Newsletter management")
 */
class ProjectEndpoints
{
    #[OA\Get(
        path: '/api/v1/newsletter/projects',
        tags: ['Newsletter'],
        summary: 'List Projects',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProjectResponse')
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/api/v1/newsletter/projects',
        tags: ['Newsletter'],
        summary: 'Create new Project',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProjectCreateRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created successfully'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/api/v1/newsletter/projects/{id}',
        tags: ['Newsletter'],
        summary: 'Get Project by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Found',
                content: new OA\JsonContent(ref: '#/components/schemas/ProjectResponse')
            ),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/api/v1/newsletter/projects/{id}',
        tags: ['Newsletter'],
        summary: 'Update existing Project',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProjectUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ProjectResponse')
            ),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/api/v1/newsletter/projects/{id}',
        tags: ['Newsletter'],
        summary: 'Delete Project by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Deleted successfully'),
            new OA\Response(response: 404, description: 'Not found')
        ]
    )]
    public function delete(): void
    {
    }
}

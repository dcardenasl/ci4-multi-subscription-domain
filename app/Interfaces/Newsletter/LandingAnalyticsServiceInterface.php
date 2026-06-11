<?php

declare(strict_types=1);

namespace App\Interfaces\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;

interface LandingAnalyticsServiceInterface
{
    public function ingest(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function overview(array $filters = [], ?SecurityContext $context = null): array;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function funnel(array $filters = [], ?SecurityContext $context = null): array;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function sessions(array $filters = [], ?SecurityContext $context = null): array;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function session(int $id, array $filters = [], ?SecurityContext $context = null): array;

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function journey(int $subscriberId, array $filters = [], ?SecurityContext $context = null): array;
}

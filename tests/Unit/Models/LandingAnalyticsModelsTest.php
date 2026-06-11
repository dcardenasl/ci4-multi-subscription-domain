<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\LandingAnalyticsDailyAggregateModel;
use App\Models\LandingAnalyticsEventModel;
use App\Models\LandingAnalyticsSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;

final class LandingAnalyticsModelsTest extends CIUnitTestCase
{
    public function testSessionModelCoversCoreLandingDimensions(): void
    {
        $fields = $this->getProtectedArrayProperty(new LandingAnalyticsSessionModel(), 'allowedFields');

        $this->assertContains('analytics_session_id', $fields);
        $this->assertContains('project_key', $fields);
        $this->assertContains('visitor_locale', $fields);
        $this->assertContains('invitation_code', $fields);
        $this->assertContains('active_seconds', $fields);
    }

    public function testEventModelCoversRawEventEnvelope(): void
    {
        $fields = $this->getProtectedArrayProperty(new LandingAnalyticsEventModel(), 'allowedFields');

        $this->assertContains('analytics_session_id', $fields);
        $this->assertContains('session_id', $fields);
        $this->assertContains('project_key', $fields);
        $this->assertContains('event_name', $fields);
        $this->assertContains('occurred_at', $fields);
    }

    public function testDailyAggregateModelCoversRetentionFriendlyCounters(): void
    {
        $fields = $this->getProtectedArrayProperty(new LandingAnalyticsDailyAggregateModel(), 'allowedFields');

        $this->assertContains('aggregate_date', $fields);
        $this->assertContains('sessions_count', $fields);
        $this->assertContains('active_seconds_total', $fields);
        $this->assertContains('abandoned_sessions_count', $fields);
    }

    /**
     * @return array<int, string>
     */
    private function getProtectedArrayProperty(object $object, string $property): array
    {
        $reflection = new ReflectionClass($object);
        $refProperty = $reflection->getProperty($property);
        $refProperty->setAccessible(true);

        /** @var array<int, string> $value */
        $value = $refProperty->getValue($object);

        return $value;
    }
}

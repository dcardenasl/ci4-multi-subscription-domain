<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Request\Newsletter;

use App\DTO\Request\Newsletter\SubscriberCreateRequestDTO;
use App\DTO\Request\Newsletter\SubscriberUpdateRequestDTO;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Guards the unified subscriber status set (pending|confirmed|unsubscribed|bounced)
 * enforced via in_list on the request DTOs.
 *
 * @internal
 */
final class SubscriberStatusValidationTest extends CIUnitTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function lifecycleStatusProvider(): array
    {
        return [
            'pending'      => ['pending'],
            'confirmed'    => ['confirmed'],
            'unsubscribed' => ['unsubscribed'],
            'bounced'      => ['bounced'],
        ];
    }

    #[DataProvider('lifecycleStatusProvider')]
    public function testCreateAcceptsEveryLifecycleStatus(string $status): void
    {
        $dto = new SubscriberCreateRequestDTO([
            'project_id' => 1,
            'email'      => 'subscriber@example.test',
            'status'     => $status,
        ], Services::validation(null, false));

        $this->assertSame($status, $dto->status);
    }

    #[DataProvider('lifecycleStatusProvider')]
    public function testUpdateAcceptsEveryLifecycleStatus(string $status): void
    {
        $dto = new SubscriberUpdateRequestDTO(['status' => $status], Services::validation(null, false));

        $this->assertSame($status, $dto->status);
    }

    public function testCreateRejectsUnknownStatus(): void
    {
        $this->expectException(ValidationException::class);

        new SubscriberCreateRequestDTO([
            'project_id' => 1,
            'email'      => 'subscriber@example.test',
            'status'     => 'archived',
        ], Services::validation(null, false));
    }

    public function testUpdateRejectsUnknownStatus(): void
    {
        $this->expectException(ValidationException::class);

        new SubscriberUpdateRequestDTO(['status' => 'archived'], Services::validation(null, false));
    }
}

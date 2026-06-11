<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Response\Newsletter;

use App\DTO\Response\Newsletter\EmailTemplateResponseDTO;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EmailTemplateResponseDTOTest extends CIUnitTestCase
{
    public function testFromArraySerializesTimeTimestampsAsNullableStrings(): void
    {
        $createdAt = Time::parse('2026-06-11 10:20:30');

        $dto = EmailTemplateResponseDTO::fromArray([
            'id' => 5,
            'project_id' => 7,
            'name' => 'Welcome',
            'subject' => 'Welcome aboard',
            'html_body' => '<p>Hello</p>',
            'text_body' => 'Hello',
            'type' => 'welcome',
            'created_at' => $createdAt,
            'updated_at' => null,
            'translations' => [],
        ]);

        $data = $dto->toArray();

        $this->assertSame('2026-06-11 10:20:30', $data['created_at']);
        $this->assertNull($data['updated_at']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration\Services\Newsletter;

use App\DTO\Request\Newsletter\CampaignIndexRequestDTO;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\PaginatedResponseDTO;

/**
 * Exercises the campaign index with project/status filters end-to-end
 * (DTO -> service -> repository -> model query).
 *
 * @internal
 */
final class CampaignIndexFilterTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('campaigns')->truncate();

        $rows = [
            ['project_id' => 1, 'name' => 'Alpha', 'subject' => 'Alpha subject', 'status' => 'draft'],
            ['project_id' => 1, 'name' => 'Beta', 'subject' => 'Beta subject', 'status' => 'scheduled'],
            ['project_id' => 2, 'name' => 'Gamma', 'subject' => 'Gamma subject', 'status' => 'scheduled'],
        ];

        foreach ($rows as $row) {
            $this->db->table('campaigns')->insert($row + [
                'html_body'       => '<p>Hello</p>',
                'text_body'       => 'Hello',
                'scheduled_at'    => date('Y-m-d H:i:s'),
                'send_started_at' => '1000-01-01 00:00:00',
                'sent_at'         => '1000-01-01 00:00:00',
                'failed_at'       => '1000-01-01 00:00:00',
                'failure_reason'  => '',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<array<string, mixed>>
     */
    private function indexRows(array $params): array
    {
        $dto = new CampaignIndexRequestDTO($params, Services::validation(null, false));
        $result = Services::campaignService(false)->index($dto);

        $this->assertInstanceOf(PaginatedResponseDTO::class, $result);

        return array_map(
            static fn (object $row): array => (array) $row->toArray(),
            $result->toArray()['data']
        );
    }

    public function testFiltersByProjectId(): void
    {
        $rows = $this->indexRows(['filter' => ['project_id' => '1']]);

        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame(1, $row['project_id']);
        }
    }

    public function testFiltersByProjectIdAndStatus(): void
    {
        $rows = $this->indexRows(['filter' => ['project_id' => '1', 'status' => 'scheduled']]);

        $this->assertCount(1, $rows);
        $this->assertSame('Beta', $rows[0]['name']);
    }

    public function testRootLevelFilterParamsAreAccepted(): void
    {
        $rows = $this->indexRows(['project_id' => 2, 'status' => 'scheduled']);

        $this->assertCount(1, $rows);
        $this->assertSame('Gamma', $rows[0]['name']);
    }
}

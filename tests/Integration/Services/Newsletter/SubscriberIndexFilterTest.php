<?php

declare(strict_types=1);

namespace Tests\Integration\Services\Newsletter;

use App\DTO\Request\Newsletter\SubscriberIndexRequestDTO;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use dcardenasl\Ci4ApiCore\Dto\PaginatedResponseDTO;

/**
 * Exercises the subscriber index with project/status filters, search and sort
 * end-to-end (DTO → service → repository → joined model query).
 *
 * @internal
 */
final class SubscriberIndexFilterTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('subscribers')->truncate();

        $rows = [
            ['project_id' => 1, 'email' => 'alpha@example.test', 'status' => 'confirmed'],
            ['project_id' => 1, 'email' => 'beta@example.test', 'status' => 'pending'],
            ['project_id' => 2, 'email' => 'gamma@example.test', 'status' => 'confirmed'],
        ];

        foreach ($rows as $row) {
            $this->db->table('subscribers')->insert($row + [
                'unsubscribe_token' => bin2hex(random_bytes(16)),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<array<string, mixed>>
     */
    private function indexRows(array $params): array
    {
        $dto = new SubscriberIndexRequestDTO($params, Services::validation(null, false));
        $result = Services::subscriberService(false)->index($dto);

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
        $rows = $this->indexRows(['filter' => ['project_id' => '1', 'status' => 'confirmed']]);

        $this->assertCount(1, $rows);
        $this->assertSame('alpha@example.test', $rows[0]['email']);
    }

    public function testRootLevelFilterParamsAreAccepted(): void
    {
        $rows = $this->indexRows(['project_id' => 2, 'status' => 'confirmed']);

        $this->assertCount(1, $rows);
        $this->assertSame('gamma@example.test', $rows[0]['email']);
    }

    public function testSortByEmailAndCreatedAtSurvivesProjectJoin(): void
    {
        $byEmail = $this->indexRows(['sort' => '-email']);
        $this->assertSame('gamma@example.test', $byEmail[0]['email']);

        $byCreated = $this->indexRows(['sort' => '-created_at']);
        $this->assertCount(3, $byCreated);
    }
}

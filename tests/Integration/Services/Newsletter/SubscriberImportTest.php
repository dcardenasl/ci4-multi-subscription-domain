<?php

declare(strict_types=1);

namespace Tests\Integration\Services\Newsletter;

use App\DTO\Request\Newsletter\SubscriberImportRequestDTO;
use App\DTO\Response\Newsletter\SubscriberImportResultResponseDTO;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use dcardenasl\Ci4ApiCore\Exceptions\NotFoundException;

/**
 * Exercises the bulk subscriber import end-to-end against the database:
 * row validation, in-file and in-table dedupe, status defaults and timestamps.
 *
 * @internal
 */
final class SubscriberImportTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    private int $projectId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('subscribers')->truncate();
        $this->db->table('projects')->truncate();

        $this->db->table('projects')->insert([
            'name'                  => 'Import Test',
            'slug'                  => 'import-test',
            'project_key'           => 'import-test-key',
            'is_active'             => 1,
            'smtp_provider'         => '',
            'smtp_host'             => '',
            'smtp_port'             => 0,
            'smtp_user'             => '',
            'smtp_pass_encrypted'   => '',
            'smtp_crypto'           => '',
            'smtp_from_name'        => 'Import Test',
            'smtp_from_email'       => 'noreply@example.test',
            'double_opt_in_enabled' => 0,
            'locale_default'        => 'es',
            'recaptcha_site_key'    => '',
            'recaptcha_secret_key'  => '',
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);
        $this->projectId = (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function import(array $params): SubscriberImportResultResponseDTO
    {
        $dto = new SubscriberImportRequestDTO($params, Services::validation(null, false));
        $result = Services::subscriberService(false)->import($dto);

        $this->assertInstanceOf(SubscriberImportResultResponseDTO::class, $result);

        return $result;
    }

    public function testImportsValidRowsAndSkipsInvalidOnes(): void
    {
        $this->db->table('subscribers')->insert([
            'project_id'        => $this->projectId,
            'email'             => 'existing@example.test',
            'status'            => 'confirmed',
            'unsubscribe_token' => bin2hex(random_bytes(16)),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $result = $this->import([
            'project_id' => $this->projectId,
            'rows' => [
                ['email' => 'NEW@Example.test', 'first_name' => 'Nina'],
                ['email' => 'pending@example.test', 'status' => 'pending', 'locale' => 'en'],
                ['email' => 'not-an-email'],
                ['email' => 'new@example.test'],
                ['email' => 'existing@example.test'],
                ['email' => 'badstatus@example.test', 'status' => 'archived'],
            ],
        ]);

        $this->assertSame(6, $result->total);
        $this->assertSame(2, $result->imported);
        $this->assertSame(4, $result->skipped);

        $reasons = array_column($result->errors, 'reason');
        $this->assertSame(['invalid_email', 'duplicate_in_file', 'already_subscribed', 'invalid_status'], $reasons);

        $imported = $this->db->table('subscribers')->where('email', 'new@example.test')->get()->getRowArray();
        $this->assertIsArray($imported);
        $this->assertSame('confirmed', $imported['status']);
        $this->assertSame('Nina', $imported['first_name']);
        $this->assertSame('es', $imported['locale']);
        $this->assertNotEmpty($imported['confirmed_at']);
        $this->assertNotEmpty($imported['unsubscribe_token']);
        $this->assertEmpty($imported['confirm_token']);

        $pending = $this->db->table('subscribers')->where('email', 'pending@example.test')->get()->getRowArray();
        $this->assertIsArray($pending);
        $this->assertSame('pending', $pending['status']);
        $this->assertSame('en', $pending['locale']);
        $this->assertEmpty($pending['confirmed_at']);
    }

    public function testImportFailsForUnknownProject(): void
    {
        $this->expectException(NotFoundException::class);

        $this->import([
            'project_id' => 99999,
            'rows' => [['email' => 'someone@example.test']],
        ]);
    }
}

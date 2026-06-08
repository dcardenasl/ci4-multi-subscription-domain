<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTrackingToDeliveriesAndCampaigns extends Migration
{
    public function up(): void
    {
        // Add columns to deliveries table
        $this->forge->addColumn('deliveries', [
            'delivery_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'provider_message_id',
            ],
            'opened_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'delivery_token',
            ],
            'clicked_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'opened_at',
            ],
            'clicks_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'clicked_at',
            ],
        ]);

        // Add index on delivery_token
        $this->db->query('CREATE INDEX deliveries_delivery_token_idx ON deliveries (delivery_token)');

        // Add columns to campaigns table
        $this->forge->addColumn('campaigns', [
            'opened_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
                'after'    => 'failure_reason',
            ],
            'clicked_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
                'after'    => 'opened_count',
            ],
        ]);
    }

    public function down(): void
    {
        if ($this->db->getPlatform() === 'SQLite3') {
            return;
        }

        $this->db->query('DROP INDEX deliveries_delivery_token_idx ON deliveries');

        // Drop columns from deliveries table
        $this->forge->dropColumn('deliveries', ['delivery_token', 'opened_at', 'clicked_at', 'clicks_count']);

        // Drop columns from campaigns table
        $this->forge->dropColumn('campaigns', ['opened_count', 'clicked_count']);
    }
}

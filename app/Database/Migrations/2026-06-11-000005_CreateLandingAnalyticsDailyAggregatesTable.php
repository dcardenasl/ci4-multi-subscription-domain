<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLandingAnalyticsDailyAggregatesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'project_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'aggregate_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'sessions_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'pageviews_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'section_views_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'scroll_events_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'form_starts_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'form_submits_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'error_events_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'conversions_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'confirmations_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'unsubscribes_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'active_seconds_total' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'abandoned_sessions_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['project_id', 'aggregate_date']);
        $this->forge->addKey(['project_key', 'aggregate_date']);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('landing_analytics_daily_aggregates');
    }

    public function down(): void
    {
        $this->forge->dropTable('landing_analytics_daily_aggregates', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLandingAnalyticsSessionsTable extends Migration
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
            'analytics_session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
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
            'subscriber_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'visitor_locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'referrer' => [
                'type'       => 'VARCHAR',
                'constraint' => 2048,
                'null'       => true,
            ],
            'utm_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'utm_medium' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'utm_campaign' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'utm_content' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'utm_term' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'invitation_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'device_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'page_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'last_seen_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'converted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'abandoned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'active_seconds' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'event_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'pageview_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'section_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'scroll_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'form_start_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'form_submit_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'error_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'conversion_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
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
        $this->forge->addKey('analytics_session_id', false, true);
        $this->forge->addKey(['project_id', 'last_seen_at']);
        $this->forge->addKey('subscriber_id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('subscriber_id', 'subscribers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('landing_analytics_sessions');
    }

    public function down(): void
    {
        $this->forge->dropTable('landing_analytics_sessions', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLandingAnalyticsEventsTable extends Migration
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
            'session_id' => [
                'type'     => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null'     => true,
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
            'event_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'page_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'section_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'form_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'occurred_at' => [
                'type' => 'DATETIME',
                'null' => false,
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
        $this->forge->addKey(['analytics_session_id', 'occurred_at']);
        $this->forge->addKey(['project_id', 'occurred_at']);
        $this->forge->addKey(['event_name', 'occurred_at']);
        $this->forge->addKey('session_id');
        $this->forge->addForeignKey('session_id', 'landing_analytics_sessions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('landing_analytics_events');
    }

    public function down(): void
    {
        $this->forge->dropTable('landing_analytics_events', true);
    }
}

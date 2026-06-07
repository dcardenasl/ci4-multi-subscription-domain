<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'project_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'null' => false,
            ],
            'smtp_provider' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_host' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_port' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'smtp_user' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_pass_encrypted' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'smtp_crypto' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_from_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_from_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'double_opt_in_enabled' => [
                'type' => 'TINYINT',
                'null' => false,
            ],
            'locale_default' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'recaptcha_site_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'recaptcha_secret_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('projects', true);
    }
}

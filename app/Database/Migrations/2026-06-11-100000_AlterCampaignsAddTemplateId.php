<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCampaignsAddTemplateId extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('campaigns', [
            'template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'project_id',
            ],
        ]);

        if ($this->db->getPlatform() !== 'SQLite3') {
            $this->db->query('ALTER TABLE campaigns ADD CONSTRAINT fk_campaigns_template_id FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    public function down(): void
    {
        if ($this->db->getPlatform() !== 'SQLite3') {
            $this->db->query('ALTER TABLE campaigns DROP FOREIGN KEY fk_campaigns_template_id');
        }
        $this->forge->dropColumn('campaigns', 'template_id');
    }
}
